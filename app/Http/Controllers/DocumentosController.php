<?php

namespace App\Http\Controllers;

class DocumentosController extends Controller
{
    public function gppei()
    {
        $path = base_path('documentacao/pdf/Guia_PEI_VF.pdf');

        abort_unless(file_exists($path), 404, 'Guia GPPEI não encontrado.');

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Guia_Pratico_PEI.pdf"',
        ]);
    }

    public function projetosPdf()
    {
        $path = base_path('documentacao/pdf/guia-pratico-de-projetos.pdf');

        abort_unless(file_exists($path), 404, 'Guia Prático de Projetos não encontrado.');

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Guia_Pratico_Projetos.pdf"',
        ]);
    }

    /**
     * Viewer embutido do Guia de Projetos com sumário lateral.
     */
    public function viewerProjetos()
    {
        return view('documentos.viewer-projetos');
    }

    /**
     * Viewer embutido do GPPEI com menu lateral de seções.
     */
    public function viewerGppei()
    {
        return view('documentos.viewer-gppei');
    }
}
