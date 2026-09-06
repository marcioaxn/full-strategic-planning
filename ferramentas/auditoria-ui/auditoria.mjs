import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const URL = process.argv[2] || 'http://localhost/fs-v1/public/';
const NOME = process.argv[3] || 'landing';
const OUT = './ui';

await fs.mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: 'new',
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const relatorio = {};

for (const vp of [
  { nome: 'desktop', width: 1366, height: 768 },
  { nome: 'notebook', width: 1280, height: 720 },
  { nome: 'tablet', width: 768, height: 1024 },
  { nome: 'celular', width: 390, height: 844 },
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: vp.width, height: vp.height, deviceScaleFactor: 1 });

  const erros = [];
  page.on('console', (m) => { if (m.type() === 'error') erros.push(m.text().slice(0, 200)); });
  page.on('pageerror', (e) => erros.push('PAGEERROR: ' + String(e).slice(0, 200)));
  const falhas = [];
  page.on('requestfailed', (r) => falhas.push(r.url().slice(0, 120) + ' :: ' + (r.failure()?.errorText || '')));

  const t0 = Date.now();
  await page.goto(URL, { waitUntil: 'networkidle2', timeout: 60000 });
  const carga = Date.now() - t0;

  await page.screenshot({ path: `${OUT}/${NOME}-${vp.nome}.png`, fullPage: vp.nome === 'desktop' });

  const dados = await page.evaluate(() => {
    const q = (s) => Array.from(document.querySelectorAll(s));
    const visivel = (el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.visibility !== 'hidden' && s.display !== 'none';
    };

    // Contraste aproximado (WCAG) do texto contra o fundo mais próximo
    const lum = (c) => {
      const m = c.match(/\d+(\.\d+)?/g);
      if (!m) return null;
      const [r, g, b] = m.slice(0, 3).map(Number).map((v) => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
      });
      return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    };
    // Sobe o DOM procurando o fundo. Se encontrar background-image (gradiente,
    // foto), devolve null: a heuristica de luminancia nao sabe ler gradiente, e
    // reportar isso como baixo contraste seria repassar erro do MEDIDOR como
    // erro do sistema.
    const fundoDe = (el) => {
      let n = el;
      while (n && n !== document.documentElement) {
        const s = getComputedStyle(n);
        if (s.backgroundImage && s.backgroundImage !== 'none') return null;
        const bg = s.backgroundColor;
        if (bg && !bg.includes('rgba(0, 0, 0, 0)') && bg !== 'transparent') return bg;
        n = n.parentElement;
      }
      return 'rgb(255,255,255)';
    };

    const baixoContraste = [];
    for (const el of q('p,span,a,li,td,th,h1,h2,h3,h4,h5,h6,label,small,button')) {
      if (!visivel(el)) continue;
      const txt = (el.textContent || '').trim();
      if (!txt || txt.length > 120) continue;
      if (el.children.length > 0) continue;
      const s = getComputedStyle(el);
      const fundo = fundoDe(el);
      if (fundo === null) continue;
      const l1 = lum(s.color), l2 = lum(fundo);
      if (l1 === null || l2 === null) continue;
      const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
      const px = parseFloat(s.fontSize);
      const grande = px >= 24 || (px >= 18.66 && parseInt(s.fontWeight) >= 700);
      const minimo = grande ? 3 : 4.5;
      if (ratio < minimo) {
        baixoContraste.push({ txt: txt.slice(0, 60), ratio: +ratio.toFixed(2), minimo, px: +px.toFixed(1), cor: s.color, fundo });
      }
    }

    // Alvos de toque menores que 44x44
    // Elementos escondidos ate receberem foco (skip link) medem 1x1 em repouso:
    // sinaliza-los como alvo pequeno seria reportar erro do MEDIDOR.
    const alvosPequenos = q('a,button,[role=button],input[type=checkbox],input[type=radio],select')
      .filter(visivel)
      .filter((el) => !el.className.toString().includes('visually-hidden'))
      .map((el) => ({ tag: el.tagName.toLowerCase(), txt: (el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 40), w: Math.round(el.getBoundingClientRect().width), h: Math.round(el.getBoundingClientRect().height) }))
      .filter((a) => a.w < 44 || a.h < 44);

    // Fonte muito pequena
    const fontesPequenas = {};
    for (const el of q('p,span,a,li,td,small,label,div')) {
      if (!visivel(el) || el.children.length > 0) continue;
      const txt = (el.textContent || '').trim();
      if (!txt) continue;
      const px = parseFloat(getComputedStyle(el).fontSize);
      if (px < 12) { const k = px.toFixed(1); fontesPequenas[k] = (fontesPequenas[k] || 0) + 1; }
    }

    return {
      titulo: document.title,
      lang: document.documentElement.lang || '(ausente)',
      metaDescription: document.querySelector('meta[name=description]')?.content || '(ausente)',
      metaViewport: document.querySelector('meta[name=viewport]')?.content || '(ausente)',
      h1: q('h1').map((e) => e.textContent.trim().slice(0, 70)),
      qtdH1: q('h1').length,
      hierarquiaTitulos: q('h1,h2,h3,h4,h5,h6').map((e) => e.tagName),
      imagensSemAlt: q('img').filter((e) => !e.getAttribute('alt')).length,
      totalImagens: q('img').length,
      inputsSemLabel: q('input,select,textarea').filter((e) => {
        if (e.type === 'hidden') return false;
        return !e.getAttribute('aria-label') && !e.getAttribute('aria-labelledby')
          && !(e.id && document.querySelector(`label[for="${e.id}"]`)) && !e.closest('label');
      }).length,
      botoesSemNome: q('button,[role=button]').filter(visivel).filter((e) => !(e.textContent || '').trim() && !e.getAttribute('aria-label') && !e.getAttribute('title')).length,
      linksSemTexto: q('a[href]').filter(visivel).filter((e) => !(e.textContent || '').trim() && !e.getAttribute('aria-label')).length,
      linksVazios: q('a[href="#"],a[href=""],a:not([href])').length,
      skipLink: !!document.querySelector('a[href^="#"][class*=skip], a[href^="#conteudo"], a[href^="#main"]'),
      landmarks: { main: q('main').length, nav: q('nav').length, header: q('header').length, footer: q('footer').length },
      scrollHorizontal: document.documentElement.scrollWidth > window.innerWidth + 1,
      larguraDocumento: document.documentElement.scrollWidth,
      larguraJanela: window.innerWidth,
      alturaDocumento: document.documentElement.scrollHeight,
      baixoContraste: baixoContraste.slice(0, 25),
      totalBaixoContraste: baixoContraste.length,
      alvosPequenos: alvosPequenos.slice(0, 15),
      totalAlvosPequenos: alvosPequenos.length,
      fontesPequenas,
      totalScripts: q('script').length,
      scriptsExternos: q('script[src]').map((e) => e.src).filter((s) => !s.includes('localhost')),
      cssExterno: q('link[rel=stylesheet]').map((e) => e.href).filter((s) => !s.includes('localhost')),
    };
  });

  relatorio[vp.nome] = { viewport: vp, tempoCargaMs: carga, errosConsole: erros, requisicoesFalhas: falhas, ...dados };
  await page.close();
}

await browser.close();
await fs.writeFile(`${OUT}/${NOME}.json`, JSON.stringify(relatorio, null, 2));
console.log('OK ->', `${OUT}/${NOME}.json`);
