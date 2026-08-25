<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class FeaturesGuideController extends Controller
{
    public function pdf()
    {
        Gate::authorize('create-task');

        $pdf = Pdf::loadView('reports.features-guide', [
            'tests' => 172,
            'assertions' => 387,
        ])
            ->setPaper('a4')
            ->setOption('defaultFont', 'Helvetica');

        return $pdf->download('RELATORIO-FUNCIONALIDADES.pdf');
    }
}
