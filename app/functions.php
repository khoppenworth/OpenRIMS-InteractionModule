<?php
class Translator
{
    private string $locale = 'en';
    private array $messages = [];
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->loadLocale('en');
    }

    public function setLocale(string $locale): void
    {
        $this->loadLocale($locale);
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function trans(string $key, array $replace = []): string
    {
        $message = $this->messages[$this->locale][$key] ?? $this->messages['en'][$key] ?? $key;

        foreach ($replace as $search => $value) {
            $message = str_replace(':' . $search, (string) $value, $message);
        }

        return $message;
    }

    private function loadLocale(string $locale): void
    {
        if (!isset($this->messages[$locale])) {
            $path = sprintf('%s/%s.php', $this->basePath, $locale);
            $this->messages[$locale] = file_exists($path) ? require $path : [];
        }
    }
}

function determine_locale(Translator $translator): string
{
    $supported = ['en', 'fr'];
    if (!empty($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $_SESSION['locale'] = $_GET['lang'];
    }

    if (!empty($_SESSION['locale']) && in_array($_SESSION['locale'], $supported, true)) {
        return $_SESSION['locale'];
    }

    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    foreach (explode(',', $header) as $segment) {
        $locale = substr($segment, 0, 2);
        if (in_array($locale, $supported, true)) {
            return $locale;
        }
    }

    return $translator->getLocale();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    return null;
}
