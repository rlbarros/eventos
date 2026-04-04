<?php

namespace App\Utils;

use App\Enum\FormModeEnum;

class FormVisibilityUtil
{
    public static function formTitle(FormModeEnum $formMode,  string $modelName, ?string $modelDescriptor): string
    {
        return match ($formMode) {
            FormModeEnum::Create => 'Cadastrar ' . $modelName,
            FormModeEnum::Edit => 'Editando ' . $modelName . ' ' . $modelDescriptor,
            FormModeEnum::View => 'Visualizando ' . $modelName . ' ' . $modelDescriptor
        };
    }

    public static function submitButtonLabel(FormModeEnum $formMode): string
    {
        return match ($formMode) {
            FormModeEnum::Create => 'Cadastrar',
            FormModeEnum::Edit => 'Editar',
            FormModeEnum::View => 'Visualizar'
        };
    }

    public static function submitButtonVisible(FormModeEnum $formMode): bool
    {
        return match ($formMode) {
            FormModeEnum::Create => true,
            FormModeEnum::Edit => true,
            FormModeEnum::View => false,
        };
    }

    public static function isReadonly(FormModeEnum $formMode): bool
    {
        return match ($formMode) {
            FormModeEnum::Create => false,
            FormModeEnum::Edit => false,
            FormModeEnum::View => true,
        };
    }

    public static function resolvePlaceholder(FormModeEnum $formMode, string $placeholder): string
    {
        return match ($formMode) {
            FormModeEnum::Create => $placeholder,
            FormModeEnum::Edit => $placeholder,
            FormModeEnum::View => '',
        };
    }
}
