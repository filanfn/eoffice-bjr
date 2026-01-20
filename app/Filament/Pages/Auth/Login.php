<?php

namespace App\Filament\Pages\Auth;


use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Actions\Action;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Masuk ke Aplikasi';
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Ingat saya');
    }

    public function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Masuk');
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label('Daftar')
            ->url(filament()->getRegistrationUrl());
    }

    public function getSubheading(): Htmlable|string|null
    {
        if (!filament()->hasRegistration()) {
            return null;
        }

        return new HtmlString('Belum punya akun? ' . $this->registerAction->toHtml());
    }
}
