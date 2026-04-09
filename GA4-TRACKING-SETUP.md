# GA4 Event Tracking Setup for Sales Funnel

This guide walks through setting up event tracking in Google Tag Manager (GTM) and Google Analytics 4 (GA4) for the sales funnel wizard on `/aanvraag`.

## Events pushed by the wizard

The wizard pushes these events to `window.dataLayer`:

| Event | When | Data |
|---|---|---|
| `sales_funnel_step_view` | A step becomes visible | `funnel_step`, `funnel_step_name` |
| `sales_funnel_step_complete` | User advances past a step | `funnel_step`, `funnel_step_name` |
| `sales_funnel_submit` | Form is submitted | `funnel_product`, `funnel_budget`, `funnel_company_type` |
| `sales_funnel_abandoned` | Page unload without submission | `funnel_step`, `funnel_step_name`, `funnel_last_completed_step` |

---

## Step 1: Open GTM

Go to [tagmanager.google.com](https://tagmanager.google.com) and select container `GTM-N75FRC56`.

---

## Step 2: Create Data Layer Variables

These tell GTM how to read the data from the `dataLayer.push()` calls.

Go to **Variables** > **User-Defined Variables** > **New**

Create these 6 variables (type: **Data Layer Variable**):

| Variable Name | Data Layer Variable Name |
|---|---|
| `DLV - Funnel Step` | `funnel_step` |
| `DLV - Funnel Step Name` | `funnel_step_name` |
| `DLV - Funnel Product` | `funnel_product` |
| `DLV - Funnel Budget` | `funnel_budget` |
| `DLV - Funnel Company Type` | `funnel_company_type` |
| `DLV - Last Completed Step` | `funnel_last_completed_step` |

For each: **New** > Variable type: **Data Layer Variable** > enter the name from the right column > **Save**.

---

## Step 3: Create Triggers

These fire when specific events are pushed to the dataLayer.

Go to **Triggers** > **New**

Create 4 triggers (type: **Custom Event**):

| Trigger Name | Event Name (exact match) |
|---|---|
| `Sales Funnel - Step View` | `sales_funnel_step_view` |
| `Sales Funnel - Step Complete` | `sales_funnel_step_complete` |
| `Sales Funnel - Submit` | `sales_funnel_submit` |
| `Sales Funnel - Abandoned` | `sales_funnel_abandoned` |

For each: **New** > Trigger type: **Custom Event** > paste the event name > **Save**.

---

## Step 4: Find your GA4 Measurement ID

Go to [analytics.google.com](https://analytics.google.com) > **Admin** (gear icon) > **Data Streams** > click your web stream > copy the **Measurement ID** (looks like `G-XXXXXXXXXX`).

If you already have a GA4 Config tag in GTM, note the Measurement ID from that tag.

---

## Step 5: Create GA4 Event Tags

Go to **Tags** > **New**

Create 4 tags (type: **Google Analytics: GA4 Event**):

### Tag 1: Funnel Step View

- Tag type: Google Analytics: GA4 Event
- Measurement ID: your `G-XXXXXXXXXX`
- Event Name: `sales_funnel_step_view`
- Event Parameters:
  - `step_number` = `{{DLV - Funnel Step}}`
  - `step_name` = `{{DLV - Funnel Step Name}}`
- Trigger: `Sales Funnel - Step View`

### Tag 2: Funnel Step Complete

- Tag type: Google Analytics: GA4 Event
- Measurement ID: your `G-XXXXXXXXXX`
- Event Name: `sales_funnel_step_complete`
- Event Parameters:
  - `step_number` = `{{DLV - Funnel Step}}`
  - `step_name` = `{{DLV - Funnel Step Name}}`
- Trigger: `Sales Funnel - Step Complete`

### Tag 3: Funnel Submit

- Tag type: Google Analytics: GA4 Event
- Measurement ID: your `G-XXXXXXXXXX`
- Event Name: `sales_funnel_submit`
- Event Parameters:
  - `product` = `{{DLV - Funnel Product}}`
  - `budget` = `{{DLV - Funnel Budget}}`
  - `company_type` = `{{DLV - Funnel Company Type}}`
- Trigger: `Sales Funnel - Submit`

### Tag 4: Funnel Abandoned

- Tag type: Google Analytics: GA4 Event
- Measurement ID: your `G-XXXXXXXXXX`
- Event Name: `sales_funnel_abandoned`
- Event Parameters:
  - `step_number` = `{{DLV - Funnel Step}}`
  - `step_name` = `{{DLV - Funnel Step Name}}`
  - `last_completed_step` = `{{DLV - Last Completed Step}}`
- Trigger: `Sales Funnel - Abandoned`

---

## Step 6: Test with Preview Mode

In GTM, click **Preview** (top right). This opens Tag Assistant.

1. Enter your URL: `https://dutchlaravelfoundation.nl/aanvraag`
2. Walk through the wizard steps
3. In Tag Assistant, verify each tag fires on the correct event
4. Check the **Variables** tab to confirm values are populated correctly

---

## Step 7: Publish

Once verified: **Submit** > add a version name like "Sales funnel tracking" > **Publish**.

---

## Step 8: Build a Funnel Report in GA4

After a day or two of data collection:

1. Go to GA4 > **Explore** > **+** (new exploration)
2. Choose **Funnel exploration**
3. Click **Steps** > **Edit**:
   - Step 1: Event = `sales_funnel_step_complete`, parameter `step_name` = `Product`
   - Step 2: `step_name` = `Omschrijving`
   - Step 3: `step_name` = `Budget`
   - Step 4: `step_name` = `Partner`
   - Step 5: `step_name` = `Contact`
   - Step 6: `step_name` = `Overzicht`
4. Set funnel type to **Closed** (must start at step 1) for accurate conversion rates

This gives you a visual funnel showing exactly where users drop off and the conversion rate at each step.

---

## Step 9 (optional): Register Custom Dimensions

To filter and segment reports by product, budget, etc.:

GA4 > **Admin** > **Custom definitions** > **Create custom dimension**:

| Dimension name | Scope | Event parameter |
|---|---|---|
| Funnel Product | Event | `product` |
| Funnel Budget | Event | `budget` |
| Funnel Company Type | Event | `company_type` |
| Funnel Step Name | Event | `step_name` |

GA4 only reports on custom parameters after you register them here. It can take up to 24 hours for new dimensions to appear in reports.
