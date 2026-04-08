<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class BrowserLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $supportedLocales = ['ar', 'en', 'fr'];
        $session = $request->getSession();

        // 1) إذا المستخدم اختار لغة يدوياً نخزنها ونستخدمها دائماً
        if ($session && $session->has('_locale')) {
            $sessionLocale = (string) $session->get('_locale');
            if (in_array($sessionLocale, $supportedLocales, true)) {
                $request->setLocale($sessionLocale);
                $this->translator->setLocale($sessionLocale);
                return;
            }
        }

        // 2) غير ذلك نختار اللغة الأقرب من المتصفح
        $preferred = $request->getPreferredLanguage($supportedLocales) ?? 'ar';
        if (!in_array($preferred, $supportedLocales, true)) {
            $preferred = 'ar';
        }

        $request->setLocale($preferred);
        $this->translator->setLocale($preferred);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}

