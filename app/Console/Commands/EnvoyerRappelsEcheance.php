<?php
// ============================================================
// app/Console/Commands/EnvoyerRappelsEcheance.php
// ============================================================
namespace App\Console\Commands;
 
use App\Services\NotificationService;
use Illuminate\Console\Command;
 
class EnvoyerRappelsEcheance extends Command
{
    protected $signature   = 'notifications:rappels-echeance';
    protected $description = 'Envoie les rappels aux élèves dont un devoir expire dans 24h';
 
    public function handle(NotificationService $notificationService): void
    {
        $this->info('📧 Envoi des rappels d\'échéance...');
        $notificationService->notifierRappelsEcheance();
        $this->info('✅ Rappels envoyés.');
    }
}
 