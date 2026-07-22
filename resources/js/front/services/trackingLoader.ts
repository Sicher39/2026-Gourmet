import type { CookiePreferences, TrackingScriptPayload } from '@/front/types/cookies';

declare global {
  interface Window {
    dataLayer?: unknown[];
    gtag?: (...args: unknown[]) => void;
    fbq?: (...args: unknown[]) => void;
    _fbq?: unknown;
    seznam_retargeting_id?: string;
    rc?: { retargetingHit?: (params: Record<string, string>) => void };
    clarity?: (...args: unknown[]) => void;
    hj?: (...args: unknown[]) => void;
    _hjSettings?: { hjid: number; hjsv: number };
  }
}

const loadedScripts = new Set<string>();

const hasConsentForScript = (script: TrackingScriptPayload, preferences: CookiePreferences): boolean => {
  if (!script.requiresConsent) {
    return true;
  }

  return preferences[script.category] === true;
};

const pathMatches = (currentPath: string, configuredPath: string): boolean => {
  return currentPath === configuredPath || currentPath.startsWith(`${configuredPath.replace(/\/$/, '')}/`);
};

const isAllowedOnCurrentPath = (script: TrackingScriptPayload): boolean => {
  const path = window.location.pathname;
  const onlyPaths = script.onlyPaths ?? [];
  const exceptPaths = script.exceptPaths ?? [];

  if (onlyPaths.length > 0 && !onlyPaths.some((allowedPath) => pathMatches(path, allowedPath))) {
    return false;
  }

  return !exceptPaths.some((blockedPath) => pathMatches(path, blockedPath));
};

const appendExternalScript = (key: string, src: string, position: TrackingScriptPayload['position'] = 'body_end'): void => {
  if (loadedScripts.has(key)) {
    return;
  }

  const script = document.createElement('script');
  script.async = true;
  script.defer = true;
  script.src = src;
  script.dataset.trackingScript = key;

  if (position === 'head') {
    document.head.appendChild(script);
  } else {
    document.body.appendChild(script);
  }

  loadedScripts.add(key);
};

const appendInlineScript = (key: string, code: string, position: TrackingScriptPayload['position'] = 'body_end'): void => {
  if (loadedScripts.has(key)) {
    return;
  }

  const script = document.createElement('script');
  script.type = 'text/javascript';
  script.text = code;
  script.dataset.trackingScript = key;

  if (position === 'head') {
    document.head.appendChild(script);
  } else {
    document.body.appendChild(script);
  }

  loadedScripts.add(key);
};

const ensureGoogleConsentMode = (): void => {
  window.dataLayer = window.dataLayer ?? [];
  window.gtag = window.gtag ?? function gtag(...args: unknown[]): void {
    window.dataLayer?.push(args);
  };

  if (!loadedScripts.has('google-consent-default')) {
    window.gtag('consent', 'default', {
      ad_storage: 'denied',
      analytics_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
      functionality_storage: 'granted',
      security_storage: 'granted',
    });
    loadedScripts.add('google-consent-default');
  }
};

export const updateGoogleConsentMode = (preferences: CookiePreferences): void => {
  if (!window.gtag) {
    return;
  }

  window.gtag('consent', 'update', {
    analytics_storage: preferences.analytics ? 'granted' : 'denied',
    ad_storage: preferences.marketing ? 'granted' : 'denied',
    ad_user_data: preferences.marketing ? 'granted' : 'denied',
    ad_personalization: preferences.marketing ? 'granted' : 'denied',
  });
};

export const prepareTrackingEnvironment = (scripts: TrackingScriptPayload[]): void => {
  if (scripts.some((script) => ['ga4', 'google_ads', 'google_tag_manager'].includes(script.provider ?? ''))) {
    ensureGoogleConsentMode();
  }
};

const loadProviderPreset = (script: TrackingScriptPayload): void => {
  const id = script.identifier?.trim();

  if (!script.provider || script.provider === 'custom' || !id) {
    return;
  }

  switch (script.provider) {
    case 'ga4':
      ensureGoogleConsentMode();
      appendExternalScript(`ga4-${id}`, `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`, script.position);
      window.gtag?.('js', new Date());
      window.gtag?.('config', id, { anonymize_ip: true });
      break;
    case 'google_ads':
      ensureGoogleConsentMode();
      appendExternalScript(`google-ads-${id}`, `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`, script.position);
      window.gtag?.('config', id);
      break;
    case 'google_tag_manager':
      window.dataLayer = window.dataLayer ?? [];
      window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
      appendExternalScript(`gtm-${id}`, `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(id)}`, script.position);
      break;
    case 'meta_pixel':
      appendInlineScript(`meta-${id}-init`, `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','${id}');fbq('track','PageView');`, script.position);
      break;
    case 'sklik':
      window.seznam_retargeting_id = id;
      appendExternalScript(`sklik-${id}`, 'https://c.seznam.cz/js/rc.js', script.position);
      break;
    case 'clarity':
      appendInlineScript(`clarity-${id}`, `(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window, document, 'clarity', 'script', '${id}');`, script.position);
      break;
    case 'hotjar': {
      const hotjarId = Number(id);

      if (!Number.isFinite(hotjarId)) {
        return;
      }

      appendInlineScript(`hotjar-${id}`, `(function(h,o,t,j,a,r){h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};h._hjSettings={hjid:${hotjarId},hjsv:6};a=o.getElementsByTagName('head')[0];r=o.createElement('script');r.async=1;r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;a.appendChild(r);})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');`, script.position);
      break;
    }
    default:
      break;
  }
};

export const loadAllowedTrackingScripts = (scripts: TrackingScriptPayload[], preferences: CookiePreferences): void => {
  updateGoogleConsentMode(preferences);

  scripts
    .filter((script) => hasConsentForScript(script, preferences))
    .filter(isAllowedOnCurrentPath)
    .forEach((script) => {
      if (script.provider === 'custom' && script.code) {
        appendInlineScript(`custom-${script.id}`, script.code, script.position);
        return;
      }

      loadProviderPreset(script);
    });
};
