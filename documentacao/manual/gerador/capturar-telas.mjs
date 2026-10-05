// Captura as telas do manual no ambiente de demonstração (http://127.0.0.1/fs-v1/public,
// banco pei_manual). Sem dependências: Node 22+ (fetch e WebSocket nativos) + Chrome.
//
//   $env:SENHA_DEMO='...'; node documentacao/manual/gerador/capturar-telas.mjs [filtro]
//
// A lista de telas fica em telas.mjs. Cada item: { arquivo, url, como?, acoes?, esperar?, alto? }
//   como   — e-mail do usuário a assumir (impersonação) antes da tela; padrão: Super Admin.
//   acoes  — código JS executado na página (async) para abrir modal, aba, seção educativa…
//   alto   — altura da janela para telas longas (padrão 900).
import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { telas, ADMIN_EMAIL, BASE } from './telas.mjs';

const aqui = dirname(fileURLToPath(import.meta.url));
const pastaImg = join(aqui, '..', 'img');
mkdirSync(pastaImg, { recursive: true });

const SENHA = process.env.SENHA_DEMO;
if (!SENHA) { console.error('Defina SENHA_DEMO.'); process.exit(1); }
const filtro = process.argv[2] ? new RegExp(process.argv[2]) : null;

const chrome = [
  process.env.CHROME_PATH,
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
].find((c) => c && existsSync(c));

const PORTA = 9333;
const perfil = join(process.env.TEMP || aqui, 'pei-manual-chrome');
const proc = spawn(chrome, [
  '--headless=new', `--remote-debugging-port=${PORTA}`, `--user-data-dir=${perfil}`,
  '--window-size=1440,900', '--hide-scrollbars', '--force-device-scale-factor=1', '--lang=pt-BR', 'about:blank',
], { stdio: 'ignore' });

const dormir = (ms) => new Promise((r) => setTimeout(r, ms));

async function conectar() {
  for (let i = 0; i < 50; i++) {
    try {
      const alvos = await (await fetch(`http://127.0.0.1:${PORTA}/json`)).json();
      const pagina = alvos.find((a) => a.type === 'page');
      if (pagina) return pagina.webSocketDebuggerUrl;
    } catch { /* ainda subindo */ }
    await dormir(200);
  }
  throw new Error('Chrome não respondeu.');
}

const ws = new WebSocket(await conectar());
await new Promise((r) => ws.addEventListener('open', r, { once: true }));
let seq = 0;
const pendentes = new Map();
ws.addEventListener('message', (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pendentes.has(msg.id)) {
    const { ok, erro } = pendentes.get(msg.id);
    pendentes.delete(msg.id);
    msg.error ? erro(new Error(msg.error.message)) : ok(msg.result);
  }
});
const cdp = (method, params = {}) => new Promise((ok, erro) => {
  const id = ++seq;
  pendentes.set(id, { ok, erro });
  ws.send(JSON.stringify({ id, method, params }));
});

await cdp('Page.enable');
await cdp('Runtime.enable');

// Ajudantes disponíveis em `acoes`: clicar pelo texto, abrir a seção educativa, esperar.
const AJUDANTES = `
  const dormir = (ms) => new Promise(r => setTimeout(r, ms));
  const visivel = (e) => !!(e && e.offsetParent !== null);
  const clicarTexto = async (texto, seletor = 'button,a,[role=tab],label') => {
    const alvo = [...document.querySelectorAll(seletor)].filter(visivel)
      .find(e => e.innerText && e.innerText.replace(/\\s+/g,' ').trim().toLowerCase().includes(texto.toLowerCase()));
    if (!alvo) throw new Error('não achei: ' + texto);
    alvo.scrollIntoView({block:'center', behavior:'instant'}); alvo.click(); await dormir(1200); return true;
  };
  const expandirEducativa = async () => {
    const c = document.querySelector('.educational-card-gradient');
    if (c && window.Alpine) { Alpine.$data(c).expanded = true; await dormir(900); c.scrollIntoView({block:'start', behavior:'instant'}); window.scrollBy({top:-80, behavior:'instant'}); }
  };
  const lw = (padrao) => { const c = Livewire.all().find(c => new RegExp(padrao).test(c.name)); if (!c) throw new Error('componente ' + padrao); return Livewire.find(c.id); };
  const chamar = async (padrao, metodo, ...args) => {
    try { await lw(padrao).call(metodo, ...args); }
    catch (e) { throw new Error(metodo + '(' + JSON.stringify(args) + ') → ' + (e?.status || '') + ' ' + String(e?.body || e?.message || '').replace(/<[^>]+>/g,' ').replace(/\\s+/g,' ').slice(0,160)); }
    await dormir(1200);
  };
  const idDoPrimeiro = (prefixo, contem) => { const b = [...document.querySelectorAll('[wire\\\\:click^="' + prefixo + '("]')].find(b => !contem || (b.closest('tr,.card,li,[wire\\\\:key]')||b.parentElement).innerText.includes(contem)); return b ? b.getAttribute('wire:click').match(/'([^']+)'/)[1] : null; };
  const rolarAte = (texto) => { const e = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6,.card-header,label,th')].filter(visivel).find(x => x.innerText && x.innerText.includes(texto)); if (e) { e.scrollIntoView({block:'start', behavior:'instant'}); window.scrollBy({top:-90, behavior:'instant'}); } };
`;

async function avaliar(expr) {
  const r = await cdp('Runtime.evaluate', { expression: `(async () => { ${AJUDANTES} ${expr} })()`, awaitPromise: true, returnByValue: true });
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description || r.exceptionDetails.text);
  return r.result.value;
}

async function ir(url) {
  await cdp('Page.navigate', { url: url.startsWith('http') ? url : BASE + url });
  // Espera a página carregar e o Livewire assentar.
  for (let i = 0; i < 60; i++) {
    await dormir(250);
    try {
      const pronto = await avaliar(`return document.readyState === 'complete' && (!window.Livewire || true);`);
      if (pronto) break;
    } catch { /* navegando */ }
  }
  await dormir(2500);
}

async function entrar(email) {
  await ir('/logout-manual-inexistente'); // limpa estado de navegação
  await cdp('Network.enable');
  await cdp('Network.clearBrowserCookies');
  await ir('/login');
  await avaliar(`
    const e = document.querySelector('input[type=email],input[name=email]');
    const s = document.querySelector('input[type=password]');
    e.value = ${JSON.stringify(email)}; e.dispatchEvent(new Event('input', {bubbles:true}));
    s.value = ${JSON.stringify(SENHA)}; s.dispatchEvent(new Event('input', {bubbles:true}));
    e.closest('form').submit();
  `);
  await dormir(3000);
  const caminho = await avaliar('return location.pathname;');
  if (caminho.endsWith('/login')) throw new Error(`Login falhou para ${email}`);
}

async function assumir(email) {
  await ir('/admin/perfis');
  const ok = await avaliar(`
    window.confirm = () => true;
    const f = [...document.querySelectorAll('form')].find(f => (f.innerText + (f.getAttribute('onsubmit')||'')).includes(${JSON.stringify(email)}) || (f.closest('tr')?.innerText||'').includes(${JSON.stringify(email)}));
    if (!f) return false; f.submit(); return true;
  `);
  if (!ok) throw new Error(`Não achei como assumir ${email}`);
  await dormir(3000);
}

async function encerrarAssuncao() {
  await avaliar(`const b = [...document.querySelectorAll('a,button')].find(e => /Encerrar Impersona/i.test(e.innerText)); b?.click(); return !!b;`);
  await dormir(3000);
}

// O admin volta sempre para a unidade raiz (AFE), onde estão os dados de planejamento:
// assumir outra pessoa deixa na sessão a unidade dela (ex.: SPG, sem identidade nem PESTEL).
async function selecionarRaiz() {
  await ir('/dashboard');
  const ok = await avaliar(`
    const c = Livewire.all().find(c => /seletor-organizacao/.test(c.name));
    if (!c) return 'sem seletor';
    const b = [...c.el.querySelectorAll('[wire\\\\:click^="selecionar("]')].find(b => b.innerText.includes('Agência Federal de Exemplo'));
    if (!b) return 'sem AFE';
    await Livewire.find(c.id).call('selecionar', b.getAttribute('wire:click').match(/'([^']+)'/)[1]);
    return 'ok';
  `);
  if (ok !== 'ok') throw new Error('selecionarRaiz: ' + ok);
  await dormir(2500);
}

await entrar(ADMIN_EMAIL);
await selecionarRaiz();
let assumido = null;
const relatorio = [];

for (const t of telas) {
  if (filtro && !filtro.test(t.arquivo)) continue;
  try {
    if ((t.como || null) !== assumido) {
      if (assumido) await encerrarAssuncao();
      if (t.como === 'visitante') { await cdp('Network.clearBrowserCookies'); }
      else if (t.como) await assumir(t.como);
      else await selecionarRaiz();
      assumido = t.como || null;
      if (assumido === null && t.como === undefined) { /* admin */ }
    }
    await cdp('Emulation.setDeviceMetricsOverride', { width: t.largura || 1440, height: t.alto || 900, deviceScaleFactor: 1, mobile: false });
    if (t.via) {
      // Chega à tela de detalhe pelos links (o id depende dos dados); um ou mais passos.
      for (const passo of [].concat(t.via)) {
        if (passo.pagina) await ir(passo.pagina);
        let href = null;
        for (let tentativa = 0; tentativa < 10 && !href; tentativa++) {
          href = await avaliar(`
            const links = [...document.querySelectorAll('a[href]')].filter(a => a.href.includes(${JSON.stringify(passo.href)}));
            const a = ${passo.contem ? `links.find(a => ((a.closest('tr') || a.closest('.card') || a.closest('li') || a.closest('div'))?.innerText||'').includes(${JSON.stringify(passo.contem)}))` : 'null'} || links[0];
            return a ? a.href : null;
          `);
          if (!href) await dormir(700);
        }
        if (!href) throw new Error(`link "${passo.href}" não encontrado`);
        await ir(href);
      }
    } else {
      await ir(t.url);
    }
    if (t.acoes) { await avaliar(t.acoes); await dormir(t.esperar ?? 1500); }
    const shot = await cdp('Page.captureScreenshot', { format: 'jpeg', quality: 82, captureBeyondViewport: false });
    writeFileSync(join(pastaImg, t.arquivo), Buffer.from(shot.data, 'base64'));
    relatorio.push(`ok   ${t.arquivo}`);
    console.log(`ok   ${t.arquivo}`);
    if (t.como === 'visitante') { await entrar(ADMIN_EMAIL); await selecionarRaiz(); assumido = null; }
  } catch (e) {
    relatorio.push(`ERRO ${t.arquivo}: ${e.message}`);
    console.log(`ERRO ${t.arquivo}: ${e.message}`);
  }
}

writeFileSync(join(aqui, 'ultima-captura.txt'), relatorio.join('\n') + '\n');
ws.close();
proc.kill();
