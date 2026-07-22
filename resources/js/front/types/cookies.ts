export type CookieCategory = 'necessary' | 'analytics' | 'marketing' | 'preferences';

export interface CookiePreferences {
  necessary: boolean;
  analytics: boolean;
  marketing: boolean;
  preferences: boolean;
}

export interface CookieSettingsPayload {
  enabled: boolean;
  version: string;
  bannerTitle: string | null;
  bannerDescription: string | null;
  acceptAllLabel: string;
  rejectAllLabel: string;
  customizeLabel: string;
  savePreferencesLabel: string;
  necessaryTitle: string | null;
  necessaryDescription: string | null;
  analyticsTitle: string | null;
  analyticsDescription: string | null;
  marketingTitle: string | null;
  marketingDescription: string | null;
  preferencesTitle: string | null;
  preferencesDescription: string | null;
  footerLinkLabel: string;
  privacyPolicyUrl: string | null;
  cookiePolicyUrl: string | null;
}

export interface TrackingScriptPayload {
  id: number;
  name: string;
  provider: 'ga4' | 'google_ads' | 'google_tag_manager' | 'meta_pixel' | 'sklik' | 'clarity' | 'hotjar' | 'adobe_fonts' | 'custom' | null;
  category: CookieCategory;
  position: 'head' | 'body_start' | 'body_end';
  identifier: string | null;
  code: string | null;
  description: string | null;
  providerName: string | null;
  providerPrivacyUrl: string | null;
  requiresConsent: boolean;
  priority: number;
  onlyPaths: string[] | null;
  exceptPaths: string[] | null;
}

export interface CookieConfigPayload {
  settings: CookieSettingsPayload;
  trackingScripts: TrackingScriptPayload[];
  legalLinks: Record<string, unknown>;
  requiresCookieConsent: boolean;
}

export const defaultCookiePreferences = (): CookiePreferences => ({
  necessary: true,
  analytics: false,
  marketing: false,
  preferences: false,
});
