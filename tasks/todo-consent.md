# Cookie Consent + Pixel/Tag Governance — Plan

Status: AWAITING APPROVAL. No code written yet. No push.

## Goal
GDPR/CCPA-friendly cookie consent banner + Google Consent Mode v2 wiring so the
existing GTM container only fires analytics/marketing tags after consent. Pixels
stay in GTM (Steve-managed) — **zero hardcoded pixels in the theme**. Everything
the owner can change lives in wp-admin.

## Architecture (matches existing idiom)
- **Admin/config** -> core plugin `SettingsPage` (`Showtime -> Settings`), new
  "Tracking & Consent" section, stored in `wp_options`. Same pattern as the
  GHL/Turnstile keys already there.
- **Rendering** -> theme `inc/consent.php` (new), required from `functions.php`.
  Mirrors `inc/popup.php`: reads options, gates on a CMS toggle, injects head
  snippets + enqueues deferred assets + renders a footer template part.
- Clean split: plugin = the knobs, theme = the output (as elsewhere).

## CMS fields (Showtime -> Settings -> "Tracking & Consent")
| Option | Type | Default |
|---|---|---|
| `showtime_gtm_id` | text `GTM-XXXXXXX` | empty |
| `showtime_gtm_inject` | checkbox - let the theme print the GTM container | off |
| `showtime_consent_enabled` | checkbox - show the banner | on |
| `showtime_consent_heading` | text | "We value your privacy" |
| `showtime_consent_message` | textarea (allows one `<a>`) | short GDPR/CCPA copy |
| `showtime_consent_accept_label` | text | "Accept all" |
| `showtime_consent_reject_label` | text | "Reject non-essential" |
| `showtime_consent_prefs_label` | text | "Preferences" |
| `showtime_consent_policy_url` | url | Privacy Policy page (`get_privacy_policy_url()`) |

`showtime_gtm_inject` lets Steve either (a) keep GTM where it is today and we only
add Consent Mode, or (b) hand GTM to the theme so we control script order. Default
off -> no risk of a double container. Documented both ways.

## Consent Mode v2 wiring (theme, `inc/consent.php`)
1. `wp_head` **priority 0** (before any plugin GTM): bootstrap `dataLayer`, define
   `gtag`, push **`consent` `default` = denied** for `ad_storage`,
   `ad_user_data`, `ad_personalization`, `analytics_storage`; `granted` for
   `functionality_storage`, `security_storage`; `wait_for_update:500`,
   `ads_data_redaction:true`, `url_passthrough:true`. Denied-by-default = safe for
   GDPR **and** CCPA. Fires whenever a GTM ID is set or the banner is on.
2. If `showtime_gtm_inject` on + ID set: print the GTM container `<script>` high in
   `<head>` and the `<noscript>` iframe on `wp_body_open`.
3. Banner choice -> JS pushes **`consent` `update`** (analytics -> `analytics_storage`;
   marketing -> `ad_storage`+`ad_user_data`+`ad_personalization`) **and** a
   `dataLayer` event `stp_consent_update` so GTM triggers can fire tags post-consent.

## The banner (theme)
- `template-parts/global/consent-banner.php` - rendered in `wp_footer`, hidden by
  default (`hidden` attr), JS reveals only when no stored choice. No-JS = stays
  denied (safe). Fixed to bottom -> **does not touch hero/LCP**.
- Buttons: Accept all / Reject non-essential / Preferences (Preferences opens an
  accessible panel with necessary[locked]+analytics+marketing toggles).
- Accessibility: `role="region"`/`aria-label`, labelled controls, keyboard
  reachable, focus moved into the Preferences dialog + Esc to close, visible focus.
- `assets/css/consent.css` (small) + `assets/js/consent.js` (deferred).
- Storage: cookie `stp_consent` = JSON `{a:0/1, m:0/1, v, t}`, **180-day** expiry +
  localStorage mirror. Valid stored choice -> apply update, never re-prompt.

## Privacy (Task 3)
- Banner links to `showtime_consent_policy_url` (defaults to the WP Privacy Policy
  page; falls back to `/legal/`). **Flag for Steve (content-side):** confirm a
  Privacy/Cookie Policy page exists and add a short cookie-policy section - that's
  wp-admin content, not code.

## Deliverable doc
- `tasks/gtm-consent-setup.md` - plain-English for Steve: which tags (GA4, Meta,
  TikTok, Google Ads) get which Consent Mode setting, where Pixel IDs go in GTM,
  and how consent gates them - so adding/swapping a pixel never needs a code change.

## Commits (one concern each, NO push)
1. `feat(consent): Tracking & Consent admin settings (core plugin)`
2. `feat(consent): Consent Mode v2 default + GTM container wiring (theme head)`
3. `feat(consent): accessible cookie banner + consent update push`
4. `docs(consent): GTM pixel-governance setup guide for Steve`

## Verification
- Headless WP boot: options read with defaults; head prints `consent default` denied
  before any GTM; `gtag`/`dataLayer` defined.
- Chrome headless: banner renders bottom (not over hero), Accept/Reject paths push
  the right `consent update`; reload after choice = no re-prompt; cookie set 180d.
- Confirm no pixel IDs anywhere in theme/plugin code.

## Review
(filled after build)
