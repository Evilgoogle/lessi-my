<?php
namespace App\Support\LeadAuto;

class Start
{
    public function run(\DateTime $date)
    {
        // Cоздаем лиды
        (new ProcessLeads())->run($date);

        // Распределяем лиды между менеджерами
        (new ProcessDistribution())->run($date);
    }
}
