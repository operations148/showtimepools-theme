# GTM + Consent Setup Guide (for Steve)

Plain-English guide. **No code changes are ever needed to add or swap a pixel** —
everything below is done inside Google Tag Manager (GTM) and the WordPress admin.

---

## 1. What the website already does for you

The theme handles consent plumbing automatically:

1. **Consent Mode v2 default = denied.** Before GTM loads on every page, the site
   tells Google "no analytics, no ads storage yet." This is the GDPR/CCPA-safe
   default. **You do NOT need to add a consent-default tag or a CMP template in
   GTM — it's already set.** Adding a second one will conflict.
2. **A cookie banner** asks the visitor to Accept / Reject / set Preferences.
3. **When the visitor chooses**, the site updates Consent Mode and pushes a
   dataLayer event called **`stp_consent_update`** that carries:
   - `stp_consent.analytics` → `true` / `false`
   - `stp_consent.marketing` → `true` / `false`

   This event also fires automatically on page load for returning visitors who
   already accepted, so your tags fire for them without a second click.

Your job in GTM is just to make each pixel **wait for the right consent**.

---

## 2. Where you edit the settings (WordPress admin)

**WP Admin → Showtime Pools → Settings → "Tracking & Consent" section:**

| Field | What it does |
|---|---|
| **GTM Container ID** | Your `GTM-XXXXXXX`. Powers Consent Mode. |
| **Inject GTM from theme** | Leave **OFF** if GTM is already on the site (plugin/header). Turn **ON** only if you want the theme to load GTM for you. |
| **Show consent banner** | Master on/off for the banner. |
| **Banner heading / message / button labels** | The banner wording. |
| **Privacy policy link** | Where the banner's link points (defaults to your Privacy Policy page). |

> Pixel IDs are **never** entered in WordPress. They live in GTM (below).

---

## 3. One-time GTM setup (5 minutes)

1. In GTM, open **Admin → Container Settings** and tick **Enable consent
   overview** (adds a shield icon to your Tags list so you can see consent state).
2. Create two **Data Layer Variables** (Variables → New → Data Layer Variable):
   - Name: `DLV - consent marketing` → Variable Name: `stp_consent.marketing`
   - Name: `DLV - consent analytics` → Variable Name: `stp_consent.analytics`
3. Create one **Custom Event trigger** you'll reuse (Triggers → New → Custom Event):
   - Name: `CE - consent update`
   - Event name: `stp_consent_update`
   - This trigger fires whenever the visitor's consent is set or replayed.

Now each pixel reuses these. Two consent buckets:

- **Analytics** cookies → controlled by `analytics_storage` / `stp_consent.analytics`
- **Marketing/ads** cookies → controlled by `ad_storage` (+ `ad_user_data`,
  `ad_personalization`) / `stp_consent.marketing`

---

## 4. The 3 pixels — step by step

For each, the pattern is identical: **paste the ID → set the consent gate →
set the trigger.** When you send me your IDs it's literally a paste.

### A) Meta / Facebook Pixel  *(marketing)*

1. **Tag:** Tags → New → choose the **Facebook Pixel** community template
   (Template Gallery → search "Facebook Pixel" by facebookarchive/Simo), or use a
   **Custom HTML** tag with Meta's base pixel snippet.
2. **Where the Pixel ID goes:** in the template's **"Facebook Pixel ID(s)"** field
   (or replace `YOUR_PIXEL_ID` in the Custom HTML snippet). This is the 15–16 digit
   ID from Meta Events Manager → Data Sources → your pixel.
3. **Consent gate (belt):** Tag → **Advanced Settings → Consent Settings →
   "Require additional consent for tag to fire"** → add **`ad_storage`**.
4. **Trigger (suspenders):** use **`CE - consent update`**, and on the trigger set
   it to fire only when **`DLV - consent marketing` equals `true`**.
   (Either make a copy of the trigger with that condition, or add the condition in
   the tag's trigger "fire on" → Some Custom Events.)

Result: the Meta Pixel only loads after the visitor allows marketing cookies.

### B) TikTok Pixel  *(marketing)*

1. **Tag:** Template Gallery → **TikTok Pixel** template (by TikTok), or a
   **Custom HTML** tag with TikTok's base pixel code.
2. **Where the Pixel ID goes:** the template's **"Pixel ID"** field (from TikTok
   Ads Manager → Assets → Events → Web Events → your pixel; it looks like
   `C1A2B3...`).
3. **Consent gate:** Advanced Settings → Consent Settings → require **`ad_storage`**.
4. **Trigger:** `CE - consent update` where **`DLV - consent marketing` = `true`**.

### C) Google Ads (remarketing + conversion tag)  *(marketing)*

Google Ads tags read Consent Mode **natively**, so they auto-respect `ad_storage`.
Still gate them so nothing fires pre-consent:

1. **Remarketing tag:** Tags → New → **Google Ads Remarketing**. Enter your
   **Conversion ID** (`AW-XXXXXXXXX`) from Google Ads → Tools → Audience manager /
   Tags.
2. **Conversion tracking:** Tags → New → **Google Ads Conversion Tracking**. Enter
   the **Conversion ID** + **Conversion Label** from the specific conversion action
   in Google Ads.
3. **Consent gate:** these tags honour `ad_storage` automatically; optionally also
   add `ad_storage` under "Require additional consent" for clarity.
4. **Trigger:** remarketing on **`CE - consent update`** with **`DLV - consent
   marketing` = `true`** (and your conversion tag on its own conversion trigger,
   which will still respect Consent Mode).

### (Bonus) GA4  *(analytics)*

GA4 natively respects `analytics_storage`. Simplest: fire your **GA4 Configuration**
tag on **All Pages** and let Consent Mode handle it — when analytics is denied GA4
sends cookieless "pings" (so you still get modeled data); when granted it switches
to full measurement. If you'd rather hard-gate it, trigger it on `CE - consent
update` where **`DLV - consent analytics` = `true`** instead.

---

## 5. How to test (GTM Preview)

1. GTM → **Preview**, enter the site URL.
2. On first load the banner shows; Meta/TikTok/Ads tags should be **Not fired**
   (consent denied).
3. Click **Accept all** → in the Preview panel you'll see the `stp_consent_update`
   event and the marketing tags move to **Fired**. The shield shows `ad_storage`
   granted.
4. Reload → tags fire on load (no second click) because the choice is remembered
   for 180 days.
5. Click **Reject non-essential** in a fresh session → marketing tags stay
   **Not fired**.

---

## 6. Swapping or adding a pixel later

Just edit the tag in GTM, paste the new ID, keep the same consent gate + trigger,
and **Submit / Publish**. The website never changes. To add a brand-new network
(e.g. Pinterest, LinkedIn), copy the Meta pattern in section 4A: paste ID → gate
`ad_storage` → trigger on `CE - consent update` with marketing = true.

---

## 7. Content note (your side, in WordPress)

The banner links to your **Privacy Policy** page. Please confirm that page exists
and add a short **cookie policy** paragraph (what categories you use: necessary,
analytics, marketing, and that visitors can change their choice anytime via the
banner). That's content you edit in WP Admin — no code needed. A visitor can
reopen the banner to withdraw consent from any link with
`data-stp-consent="open"` (tell me if you want a "Cookie settings" link added to
the footer).
