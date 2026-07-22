<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { defaultCookiePreferences, type CookiePreferences } from '@/front/types/cookies';
import { useCookieConsent } from '@/front/composables/useCookieConsent';

const {
  config,
  preferences,
  isBannerVisible,
  isPreferencesOpen,
  requiresCookieConsent,
  loadConfig,
  acceptAll,
  rejectAll,
  savePreferences,
  withdraw,
  openPreferences,
} = useCookieConsent();

const localPreferences = ref<CookiePreferences>(defaultCookiePreferences());
const panelRef = ref<HTMLDivElement | null>(null);

const settings = computed(() => config.value?.settings);
const hasOptionalCookieSettings = computed(() => {
  if (!config.value || !settings.value?.enabled) {
    return false;
  }

  return config.value.trackingScripts.some(
    (script) => script.requiresConsent && (script.category === 'analytics' || script.category === 'marketing' || script.category === 'preferences'),
  );
});

const categories = computed(() => [
  {
    key: 'necessary' as const,
    title: settings.value?.necessaryTitle ?? 'Nezbytné cookies',
    description: settings.value?.necessaryDescription ?? 'Nutné pro fungování webu.',
    disabled: true,
  },
  {
    key: 'analytics' as const,
    title: settings.value?.analyticsTitle ?? 'Analytické cookies',
    description: settings.value?.analyticsDescription ?? 'Pomáhají nám měřit návštěvnost a zlepšovat web.',
    disabled: false,
  },
  {
    key: 'marketing' as const,
    title: settings.value?.marketingTitle ?? 'Marketingové cookies',
    description: settings.value?.marketingDescription ?? 'Pomáhají s měřením a cílením reklamních kampaní.',
    disabled: false,
  },
  {
    key: 'preferences' as const,
    title: settings.value?.preferencesTitle ?? 'Preferenční cookies',
    description: settings.value?.preferencesDescription ?? 'Ukládají volby a nastavení návštěvníka.',
    disabled: false,
  },
]);

const syncLocalPreferences = (): void => {
  localPreferences.value = { ...preferences.value, necessary: true };
};

const handleSave = async (): Promise<void> => {
  await savePreferences(localPreferences.value);
};

const handleReopen = (): void => {
  if (!hasOptionalCookieSettings.value) {
    return;
  }

  syncLocalPreferences();
  openPreferences();
};

const handleEscape = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && isPreferencesOpen.value) {
    isPreferencesOpen.value = false;
  }
};

watch(isPreferencesOpen, async (isOpen) => {
  if (!isOpen) {
    return;
  }

  syncLocalPreferences();
  await nextTick();
  panelRef.value?.focus();
});

onMounted(async () => {
  await loadConfig();
  window.addEventListener('open-cookie-settings', handleReopen);
  window.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
  window.removeEventListener('open-cookie-settings', handleReopen);
  window.removeEventListener('keydown', handleEscape);
});
</script>

<template>
  <Teleport to="body">
    <div>
      <button
        v-if="hasOptionalCookieSettings && settings"
        type="button"
        class="fixed right-5 bottom-5 z-40 rounded-full border border-accent/50 bg-dark/95 p-3 text-accent shadow-xl backdrop-blur transition hover:border-accent hover:text-light focus:outline-none focus:ring-2 focus:ring-accent"
        aria-label="Otevřít nastavení cookies"
        title="Nastavení cookies"
        @click="openPreferences"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          class="h-6 w-6"
          aria-hidden="true"
        >
          <circle cx="12" cy="12" r="9" />
          <circle cx="9" cy="9" r="1.3" fill="currentColor" stroke="none" />
          <circle cx="14.5" cy="8" r="1" fill="currentColor" stroke="none" />
          <circle cx="15.5" cy="13.5" r="1.2" fill="currentColor" stroke="none" />
          <circle cx="8" cy="14.5" r="0.9" fill="currentColor" stroke="none" />
        </svg>
      </button>

      <div
        v-if="isBannerVisible && requiresCookieConsent && settings"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-accent/50 bg-dark/95 p-4 shadow-2xl backdrop-blur md:p-6"
        role="dialog"
        aria-live="polite"
        aria-label="Nastavení cookies"
      >
        <div class="mx-auto flex max-w-6xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="max-w-3xl">
            <h2 class="font-main text-xl font-bold text-accent">
              {{ settings.bannerTitle ?? 'Nastavení cookies' }}
            </h2>
            <p class="mt-2 font-main text-base text-light/90">
              {{ settings.bannerDescription ?? 'Používáme nezbytné technické cookies. Analytické a marketingové nástroje spustíme pouze po vašem souhlasu.' }}
            </p>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row md:flex-col lg:flex-row">
            <button
              type="button"
              class="rounded-xl border border-light/50 px-5 py-3 font-main text-sm font-bold text-light transition hover:border-light hover:bg-light hover:text-dark focus:outline-none focus:ring-2 focus:ring-accent"
              @click="rejectAll"
            >
              {{ settings.rejectAllLabel }}
            </button>

            <button
              v-if="hasOptionalCookieSettings"
              type="button"
              class="rounded-xl border border-light/50 px-5 py-3 font-main text-sm font-bold text-light transition hover:border-light hover:bg-light hover:text-dark focus:outline-none focus:ring-2 focus:ring-accent"
              @click="openPreferences"
            >
              {{ settings.customizeLabel }}
            </button>

            <button
              type="button"
              class="rounded-xl bg-accent px-5 py-3 font-main text-sm font-bold text-dark transition hover:bg-light focus:outline-none focus:ring-2 focus:ring-accent"
              @click="acceptAll"
            >
              {{ settings.acceptAllLabel }}
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="isPreferencesOpen && hasOptionalCookieSettings && settings"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-preferences-title"
      >
        <div
          ref="panelRef"
          tabindex="-1"
          class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-accent/50 bg-dark p-6 text-light shadow-2xl focus:outline-none"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 id="cookie-preferences-title" class="font-main text-2xl font-bold text-accent">
                {{ settings.customizeLabel }}
              </h2>
              <p class="mt-2 font-main text-sm text-light/80">
                Nezbytné cookies jsou vždy aktivní. Ostatní kategorie jsou dobrovolné a můžete je kdykoli změnit.
              </p>
            </div>
            <button
              type="button"
              class="rounded-full border border-light/40 px-3 py-1 text-sm text-light hover:border-light focus:outline-none focus:ring-2 focus:ring-accent"
              aria-label="Zavřít nastavení cookies"
              @click="isPreferencesOpen = false"
            >
              ×
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <label
              v-for="category in categories"
              :key="category.key"
              class="flex items-start justify-between gap-4 rounded-2xl border border-light/15 p-4"
            >
              <span>
                <span class="block font-main text-lg font-bold text-light">{{ category.title }}</span>
                <span class="mt-1 block font-main text-sm text-light/75">{{ category.description }}</span>
              </span>
              <input
                v-model="localPreferences[category.key]"
                type="checkbox"
                class="mt-1 h-5 w-5 rounded border-light text-accent focus:ring-accent"
                :disabled="category.disabled"
                :aria-label="category.title"
              />
            </label>
          </div>

          <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-between">
            <button type="button" class="rounded-xl border border-light/50 px-5 py-3 font-main text-sm font-bold text-light transition hover:border-light hover:bg-light hover:text-dark focus:outline-none focus:ring-2 focus:ring-accent" @click="rejectAll">
              {{ settings.rejectAllLabel }}
            </button>
            <button type="button" class="rounded-xl border border-light/50 px-5 py-3 font-main text-sm font-bold text-light transition hover:border-light hover:bg-light hover:text-dark focus:outline-none focus:ring-2 focus:ring-accent" @click="withdraw">
              Odvolat souhlas
            </button>
            <button type="button" class="rounded-xl bg-accent px-5 py-3 font-main text-sm font-bold text-dark transition hover:bg-light focus:outline-none focus:ring-2 focus:ring-accent" @click="handleSave">
              {{ settings.savePreferencesLabel }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
