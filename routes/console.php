<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tarefas:notificar-prazos-proximos')->dailyAt('08:00');
Schedule::command('tarefas:notificar-atrasadas')->dailyAt('08:10');
Schedule::command('tarefas:enviar-resumo-tarefas-abertas')->dailyAt('08:30');
Schedule::command('tarefas:gerar-recorrentes')->everyTenMinutes();
