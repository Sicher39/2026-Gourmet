import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  defaultCookiePreferences,
  type CookieConfigPayload,
  type CookiePreferences,
  type TrackingScriptPayload,
} from '@/front/types/cookies';
import { loadAllowedTrackingScripts, prepareTrackingEnvironment, updateGoogleConsentMode } from '@/front/services/trackingLoader';

const storageKeys = {
  uuid: 'cookie_consent_uuid',
  preferences: 'cookie_consent_preferences',
  version: 'cookie_consent_version',
};

const config = ref<CookieConfigPayload | null>(null);
const preferences = ref<CookiePreferences>(defaultCookiePreferences());
const isLoaded = ref(false);
const isBannerVisible = ref(false);
const isPreferencesOpen = ref(false);
let inertiaNavigationListenerRegistered = false;

const createUuid = (): string => {
  if (window.crypto?.randomUUID) {
    return window.crypto.randomUUID();
  }

  return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
};

const getConsentUuid = (): string => {
  const existing = localStorage.getItem(storageKeys.uuid);

  if (existing) {
    return existing;
  }

  const uuid = createUuid();
  localStorage.setItem(storageKeys.uuid, uuid);

  return uuid;
};

const readStoredPreferences = (): CookiePreferences | null => {
  const rawPreferences = localStorage.getItem(storageKeys.preferences);

  if (!rawPreferences) {
    return null;
  }

  try {
    return { ...defaultCookiePreferences(), ...JSON.parse(rawPreferences), necessary: true };
  } catch {
    return null;
  }
};

const hasValidStoredConsent = (version: string): boolean => {
  return localStorage.getItem(storageKeys.version) === version && readStoredPreferences() !== null;
};

const csrfToken = (): string => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const persistConsent = async (nextPreferences: CookiePreferences, withdrawal = false): Promise<void> => {
  if (!config.value) {
    return;
  }

  localStorage.setItem(storageKeys.version, config.value.settings.version);
  localStorage.setItem(storageKeys.preferences, JSON.stringify(nextPreferences));
  preferences.value = nextPreferences;

  try {
    const response = await fetch('/api/compliance/consent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        consent_uuid: getConsentUuid(),
        type: 'cookie',
        version: config.value.settings.version,
        preferences: nextPreferences,
        withdrawal,
      }),
    });

    if (!response.ok) {
      console.warn('Cookie consent could not be stored on the server.');
    }
  } catch {
    console.warn('Cookie consent could not be stored on the server.');
  }
};

const applyPreferences = (nextPreferences: CookiePreferences): void => {
  updateGoogleConsentMode(nextPreferences);
  loadAllowedTrackingScripts(config.value?.trackingScripts ?? [], nextPreferences);
};

/**
 * Determine whether any optional category was revoked (true → false).
 */
const anyConsentRevoked = (oldPreferences: CookiePreferences, newPreferences: CookiePreferences): boolean => {
  const optionalKeys: (keyof CookiePreferences)[] = ['analytics', 'marketing', 'preferences'];

  return optionalKeys.some((key) => oldPreferences[key] === true && newPreferences[key] === false);
};

/**
 * When optional consent is revoked (previously granted, now denied),
 * forcefully reload the page to ensure all third-party providers stop.
 * First-time rejection with no prior grant does not trigger a reload.
 */
const reloadIfConsentRevoked = (oldPreferences: CookiePreferences, newPreferences: CookiePreferences): void => {
  if (anyConsentRevoked(oldPreferences, newPreferences)) {
    window.location.reload();
  }
};

const setupInertiaNavigationReload = (scripts: TrackingScriptPayload[]): void => {
  if (inertiaNavigationListenerRegistered) {
    return;
  }

  router.on('navigate', () => {
    loadAllowedTrackingScripts(scripts, preferences.value);
  });

  inertiaNavigationListenerRegistered = true;
};

export const useCookieConsent = () => {
  const requiresCookieConsent = computed(() => Boolean(config.value?.settings.enabled && config.value.requiresCookieConsent));

  const loadConfig = async (): Promise<void> => {
    if (isLoaded.value) {
      return;
    }

    try {
      const response = await fetch('/api/compliance/cookie-config', {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        preferences.value = defaultCookiePreferences();
        isBannerVisible.value = false;
        isLoaded.value = true;
        return;
      }

      config.value = await response.json() as CookieConfigPayload;
      prepareTrackingEnvironment(config.value.trackingScripts);

      const storedPreferences = readStoredPreferences();

      if (storedPreferences && hasValidStoredConsent(config.value.settings.version)) {
        applyPreferences(storedPreferences);
      } else {
        preferences.value = defaultCookiePreferences();
        isBannerVisible.value = requiresCookieConsent.value;
      }

      if (!requiresCookieConsent.value) {
        isBannerVisible.value = false;
        applyPreferences(defaultCookiePreferences());
      }

      setupInertiaNavigationReload(config.value.trackingScripts);
      isLoaded.value = true;
    } catch {
      preferences.value = defaultCookiePreferences();
      isBannerVisible.value = false;
      isLoaded.value = true;
    }
  };

  const acceptAll = async (): Promise<void> => {
    const nextPreferences: CookiePreferences = {
      necessary: true,
      analytics: true,
      marketing: true,
      preferences: true,
    };

    await persistConsent(nextPreferences);
    applyPreferences(nextPreferences);
    isBannerVisible.value = false;
    isPreferencesOpen.value = false;
  };

  const rejectAll = async (): Promise<void> => {
    const nextPreferences = defaultCookiePreferences();
    const previousPreferences = { ...preferences.value };

    await persistConsent(nextPreferences);
    applyPreferences(nextPreferences);
    preferences.value = nextPreferences;
    isBannerVisible.value = false;
    isPreferencesOpen.value = false;

    reloadIfConsentRevoked(previousPreferences, nextPreferences);
  };

  const savePreferences = async (nextPreferences: CookiePreferences): Promise<void> => {
    const normalizedPreferences: CookiePreferences = { ...nextPreferences, necessary: true };
    const previousPreferences = { ...preferences.value };

    await persistConsent(normalizedPreferences);
    applyPreferences(normalizedPreferences);
    preferences.value = normalizedPreferences;
    isBannerVisible.value = false;
    isPreferencesOpen.value = false;

    reloadIfConsentRevoked(previousPreferences, normalizedPreferences);
  };

  const withdraw = async (): Promise<void> => {
    const nextPreferences = defaultCookiePreferences();
    const previousPreferences = { ...preferences.value };

    await persistConsent(nextPreferences, true);
    applyPreferences(nextPreferences);
    preferences.value = nextPreferences;
    isPreferencesOpen.value = false;

    reloadIfConsentRevoked(previousPreferences, nextPreferences);
  };

  const openPreferences = (): void => {
    if (requiresCookieConsent.value) {
      isPreferencesOpen.value = true;
      return;
    }

    router.visit(config.value?.settings.cookiePolicyUrl ?? '/cookies');
  };

  return {
    config,
    preferences,
    isLoaded,
    isBannerVisible,
    isPreferencesOpen,
    requiresCookieConsent,
    loadConfig,
    acceptAll,
    rejectAll,
    savePreferences,
    withdraw,
    openPreferences,
  };
};
