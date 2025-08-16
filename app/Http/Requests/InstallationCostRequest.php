<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallationCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'installationType' => 'required|in:string,storage',
            'panelCount' => 'required|integer|min:1',
            'panelType' => 'required|in:tongwei,jasolar',
            'storageCapacity' => 'nullable|integer|in:0,5,10,15,20',
            'hybridInverter' => 'boolean',
            'groundInstallation' => 'nullable|in:grunt,ekierka,gont,dachowka,rąbek',
            'additionalInverterKW' => 'nullable|integer|min:0',
            'backup' => 'boolean',
            'proJoy' => 'boolean',
            'extraStorage' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'installationType.required' => 'Wybierz rodzaj instalacji.',
            'panelCount.required' => 'Podaj ilość paneli.',
            'panelCount.min' => 'Minimalna ilość paneli to 1.',
            'panelType.required' => 'Wybierz typ paneli.',
            'storageCapacity.in' => 'Nieprawidłowa pojemność magazynu.',
            'hybridInverter.boolean' => 'Nieprawidłowa wartość dla inwertera hybrydowego.',
            'groundInstallation.in' => 'Nieprawidłowy typ montażu.',
            'additionalInverterKW.min' => 'Dodatkowa moc inwertera nie może być ujemna.',
            'extraStorage.min' => 'Dodatkowa pojemność magazynu nie może być ujemna.',
        ];
    }
}
