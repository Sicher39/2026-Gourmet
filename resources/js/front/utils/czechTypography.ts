const NBSP = '\u00A0'
const HTML_NBSP = '&nbsp;'

type TypographyMode = 'text' | 'html'

const SINGLE_LETTER_WORDS = /(^|[\s([{"„])([AaIiKkOoSsUuVvZz])\s+(?=\S)/g
const ABBREVIATIONS = /(^|[\s([{"„])((?:čl|odst|písm|str|č|tel|mob)\.)\s+(?=\S)/gi
const NUMBER_WITH_FOLLOWING_TOKEN = /(\d+(?:[ .,]\d+)*)(?:\s+)(?=(?:%|°C|kW|kWh|MWh|MW|Wp|W|V|A|Ah|Kč|EUR|m|m2|m²|m3|m³|cm|mm|kg|t|ks|let|roku|roků|dní|hodin|hodiny|hod|Sb\.|CZK|\S))/g

function spacer(mode: TypographyMode): string {
  return mode === 'html' ? HTML_NBSP : NBSP
}

function applyTypography(value: string, mode: TypographyMode): string {
  if (!value) return ''

  const joiner = spacer(mode)

  return value
    .replace(SINGLE_LETTER_WORDS, `$1$2${joiner}`)
    .replace(ABBREVIATIONS, `$1$2${joiner}`)
    .replace(NUMBER_WITH_FOLLOWING_TOKEN, `$1${joiner}`)
}

export function nbspText(value: string): string {
  return applyTypography(value, 'text')
}

export function nbspHtml(value: string): string {
  return value
    .split(/(<[^>]+>)/g)
    .map((part) => (part.startsWith('<') ? part : applyTypography(part, 'html')))
    .join('')
}

export function mapTypography<T>(value: T, mode: TypographyMode = 'text'): T {
  if (typeof value === 'string') {
    return (mode === 'html' ? nbspHtml(value) : nbspText(value)) as T
  }

  if (Array.isArray(value)) {
    return value.map((item) => mapTypography(item, mode)) as T
  }

  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.entries(value as Record<string, unknown>).map(([key, item]) => [
        key,
        mapTypography(item, mode)
      ])
    ) as T
  }

  return value
}

const SKIPPED_KEYS = new Set([
  'url',
  'href',
  'src',
  'path',
  'route',
  'routeName',
  'route_name',
  'slug',
  'key',
  'id',
  'uuid',
  'email',
  'phone',
  'logoPath',
  'logoUrl',
  'logoDarkPath',
  'logoDarkUrl',
  'ogImage',
  'ogUrl',
  'twitterImage',
  'canonical',
  'social_facebook_url',
  'social_instagram_url',
  'social_linkedin_url',
  'social_youtube_url',
])

const URL_OR_PATH_VALUE = /^(?:https?:\/\/|mailto:|tel:|\/|#|[\w.-]+@[\w.-]+\.[a-z]{2,})/i

function shouldTransformString(key: string | null, value: string): boolean {
  if (key !== null && SKIPPED_KEYS.has(key)) {
    return false
  }

  const trimmed = value.trim()

  return trimmed !== '' && !URL_OR_PATH_VALUE.test(trimmed)
}

function transformBackendValue(value: unknown, key: string | null = null): unknown {
  if (typeof value === 'string') {
    return shouldTransformString(key, value) ? nbspText(value) : value
  }

  if (Array.isArray(value)) {
    return value.map((item) => transformBackendValue(item, null))
  }

  if (value && typeof value === 'object') {
    const record = value as Record<string, unknown>

    for (const [childKey, childValue] of Object.entries(record)) {
      record[childKey] = transformBackendValue(childValue, childKey)
    }
  }

  return value
}

/**
 * Apply Czech typography to all backend-provided Inertia page props.
 * Technical values such as URLs, paths, slugs, ids and contact links are skipped.
 */
export function transformPageProps(props: Record<string, unknown>): void {
  transformBackendValue(props)
}
