<?php

namespace App\Filament\Pages\Auth;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Actions\Action;

class Register extends BaseRegister
{
    public function getHeading(): string
    {
        return 'Daftar Akun Baru';
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Lengkap')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule('min:8')
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Konfirmasi Kata Sandi')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->label('Daftar');
    }

    public function loginAction(): Action
    {
        return parent::loginAction()
            ->label('Masuk');
    }

    public function getSubheading(): Htmlable|string|null
    {
        if (!filament()->hasLogin()) {
            return null;
        }

        return new HtmlString('Sudah punya akun? ' . $this->loginAction->toHtml());
    }
}
