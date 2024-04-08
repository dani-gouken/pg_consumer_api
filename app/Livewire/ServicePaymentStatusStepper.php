<?php

namespace App\Livewire;

use App\Models\ServicePayment;
use App\Models\ServicePaymentStatusEnum;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ServicePaymentStatusStepper extends Component
{
    public ServicePayment $payment;
    public function mount(string $code): void
    {
        $this->payment = ServicePayment::findByCodeOrFail($code);
    }
    public function render(): View
    {
        return view(
            'livewire.service-payment-status-stepper',
            ['payment' => $this->payment, 'steps' => $this->buildSteps()]
        );
    }

    /**
     * @return array<array<string,string>>
     */
    public function buildSteps(): array
    {
        $status = $this->payment->status;
        return [
            ...$this->initStep($status),
            ...$this->debitStep($status),
            ...$this->creditStep($status),

        ];
    }

     /**
     * @return array<array<string,string>>
     */
    public function initStep(ServicePaymentStatusEnum $status): array
    {
        return match ($status) {
            ServicePaymentStatusEnum::draft => [
                [
                    'title' => 'Initialisation',
                    'description' => " Veuillez patienter, votre paiement est en cours d'initialisation",
                    'status' => "ongoing"
                ]
            ],
            ServicePaymentStatusEnum::initError => [
                [
                    'title' => 'Erreur',
                    'description' => "Une érreur est survenu lors de l'initialisation du paiement",
                    'status' => "error"
                ]
            ],
            default => [
                [
                    'title' => 'Initialisation',
                    'description' => "Le paiement a été initialisé",
                    'status' => "success"
                ]
            ],
        };
    }

     /**
     * @return array<array<string,string>>
     */
    public function creditStep(ServicePaymentStatusEnum $status): array
    {
        return match ($status) {
            ServicePaymentStatusEnum::creditPending => [
                [
                    'title' => 'Achat du service en cours',
                    'description' => "Votre service est en cours d'achat",
                    'status' => "ongoing"
                ]
            ],
            ServicePaymentStatusEnum::success => [
                [
                    'title' => "Succès",
                    'description' => "Le service a été acheté avec succès",
                    'status' => "complete"
                ]
            ],
            ServicePaymentStatusEnum::creditError => [
                [
                    'title' => "Echec de l'achat du service",
                    'description' => "L'achat du service a échouer, notre support technique se chargera d'investiguer cette transaction. " .
                        "Veuillez contacter notre service client pour en savoir plus",
                    'status' => "error"
                ]
            ],
            default => [
            ],
        };
    }

    /**
     * @return array<array<string,string>>
     */
    public function debitStep(ServicePaymentStatusEnum $status): array
    {
        return match ($status) {

            ServicePaymentStatusEnum::debitPending => [
                [
                    'title' => 'En attente de paiement',
                    'description' => "Veuillez patienter, votre paiement est en cours d'initialisation",
                    'status' => "ongoing"
                ]
            ],
            ServicePaymentStatusEnum::debitError => [
                [
                    'title' => 'Echec du paiement',
                    'description' => "Le paiement à échoué, veuillez vérifier votre solde ou retentez ultérieurement",
                    'status' => "error"
                ]
            ],
            ServicePaymentStatusEnum::creditPending, ServicePaymentStatusEnum::creditError, ServicePaymentStatusEnum::success => [
                [
                    'title' => 'Paiement',
                    'description' => "Nous avons reçu votre paiement avec succès",
                    'status' => "success"
                ]
            ],
            default => [
            ],
        };
    }

    public function stepBarColor(string $status): string
    {
        return match ($status) {
            'complete' => 'bg-green-600',
            'success' => 'bg-indigo-600',
            'error' => 'bg-red-600',
            default => 'bg-gray-300'
        };
    }
    public function stepIconClass(string $status): string
    {
        return match ($status) {
            'success' => 'bg-indigo-600 group-hover:bg-indigo-800',
            'complete' => 'bg-green-600 group-hover:bg-green-800',
            'ongoing' => 'border-2 border-indigo-600 bg-white',
            'error' => 'bg-red-600 group-hover:bg-red-800 text-red-500',
            'awaiting' => 'border-2 border-gray-300 bg-white group-hover:border-gray-400',
            default => '',
        };
    }
    public function stepIcon(string $status): string
    {
        return match ($status) {
            'success', 'complete' => '<svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
            </svg>',
            'error' => '<svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>',
            'ongoing' => '<span class="h-2.5 w-2.5 rounded-full bg-indigo-600 animate-ping"></span>',
            'awaiting' => '<span class="h-2.5 w-2.5 rounded-full bg-transparent group-hover:bg-gray-300"></span>',
            default => '',
        };
    }
}