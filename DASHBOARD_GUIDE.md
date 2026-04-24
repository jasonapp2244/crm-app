# Krayin CRM - Complete Dashboard & Feature Guide

## Quick Start

### Login
- **URL:** http://127.0.0.1:8000
- **Email:** `admin@example.com`
- **Password:** `admin123`

---

## Sidebar Navigation

| # | Menu | Sub-menus | Description |
|---|------|-----------|-------------|
| 1 | Dashboard | - | Overview stats, charts, revenue |
| 2 | Leads | - | Pipeline-based lead management |
| 3 | Quotes | - | Proposals with line items |
| 4 | Mail | Inbox, Draft, Outbox, Sent, Trash | Email management |
| 5 | SMS & WhatsApp | Messages, Twilio Numbers | Twilio messaging |
| 6 | Activities | - | Calls, meetings, notes, tasks |
| 7 | Contacts | Persons, Organizations | CRM contacts |
| 8 | Products | - | Product catalog |
| 9 | Settings | Users, Leads, Inventory, Automation, Tags | System config |
| 10 | Configuration | - | Global configuration |

---

## 1. Dashboard

After login, you land on the Dashboard. It shows a real-time overview of your CRM data.

### Date Filters
- **Start Date** and **End Date** pickers at the top-right
- All widgets update automatically when you change dates

### Left Section Widgets

#### 1.1 Revenue Stats
- **Won Revenue** - Total revenue from won leads (green)
- **Lost Revenue** - Total revenue from lost leads (red)
- Shows percentage change compared to the previous period
- Horizontal bar chart comparing Won vs Lost

#### 1.2 Overall Stats (6 Cards)
| Card | Description |
|------|-------------|
| Average Lead Value | Average monetary value per lead |
| Total Leads | Number of leads in the selected period |
| Average Leads Per Day | Daily lead creation rate |
| Total Quotations | Number of quotes created |
| Total Persons | Number of contacts |
| Total Organizations | Number of companies |

Each card shows the current value and a percentage change indicator.

#### 1.3 Total Leads Chart
- Stacked bar chart showing leads over time
- **Purple bars** = All Leads
- **Cyan bars** = Won Leads
- **Coral bars** = Lost Leads

#### 1.4 Top Selling Products
- Top 5 products ranked by revenue
- Shows product name and total revenue
- Click a product name to view its details

#### 1.5 Top Persons
- Top 5 contacts by activity/revenue
- Shows name, avatar, and email
- Click a person to view their profile

### Right Section Widgets (Sidebar)

#### 1.6 Open Leads by States
- **Funnel chart** showing how leads move through pipeline stages
- Each stage shows count and name
- Visualizes your sales pipeline health

#### 1.7 Revenue by Sources
- **Doughnut chart** showing where revenue comes from
- Sources: Web, Email, Phone, Direct, etc.
- Shows percentage breakdown per source

#### 1.8 Revenue by Types
- **Doughnut chart** showing revenue by lead type
- Types as configured in Settings > Lead > Types

#### 1.9 SMS & WhatsApp Stats
- **Total Sent** - All outbound messages (green)
- **Total Received** - All inbound messages (blue)
- **Total Failed** - Failed messages (red)
- **Today Sent** / **Today Received** - Today's counts
- **"View All"** link goes to the SMS dashboard

---

## 2. Leads Management

**Path:** Sidebar > Leads

### How to Create a Lead
1. Click **"Create Lead"** button
2. Fill in:
   - **Title** - Lead name (e.g., "Website Redesign Project")
   - **Lead Value** - Expected deal value
   - **Person** - Link to a contact
   - **Source** - Where the lead came from
   - **Type** - Lead category
   - **Pipeline** - Which sales pipeline
   - **Stage** - Current stage in the pipeline
   - **Expected Close Date**
3. Click **Save**

### Kanban View
- Drag and drop leads between pipeline stages
- Visual card-based view of your pipeline

### Lead Actions
- **View** - See full lead details, activities, emails, quotes
- **Edit** - Update lead information
- **Add Products** - Attach products to the lead
- **Add Activities** - Log calls, meetings, notes
- **Send Email** - Email directly from lead view
- **Create Quote** - Generate a proposal
- **Add Tags** - Categorize with color tags
- **Delete** - Remove the lead
- **Mass Update/Delete** - Bulk operations via checkboxes

---

## 3. Quotes Management

**Path:** Sidebar > Quotes

### How to Create a Quote
1. Click **"Create Quote"**
2. Fill in:
   - **Subject** - Quote title
   - **Person** - Customer contact
   - **Lead** - Associated lead (optional)
   - **Billing/Shipping Address**
   - **Add Items** - Products with quantity, price, discount
3. Click **Save**

### Quote Actions
- **Edit** - Modify the quote
- **Print** - Generate PDF of the quote
- **Delete** / **Mass Delete**

---

## 4. Mail (Email)

**Path:** Sidebar > Mail

### Folders
| Folder | Description |
|--------|-------------|
| Inbox | Received emails |
| Draft | Saved drafts |
| Outbox | Emails being sent |
| Sent | Successfully sent emails |
| Trash | Deleted emails |

### How to Send an Email
1. Click **"Compose Mail"**
2. Fill in:
   - **To** - Recipient email(s)
   - **CC / BCC** - Optional
   - **Subject** - Email subject
   - **Message** - Rich text editor (TinyMCE)
   - **Attachments** - Click attachment icon
3. Click **Send** or **Draft** to save

### Email Features
- Email threading (replies grouped together)
- Tag emails with color labels
- Link emails to leads/contacts
- Inbound email parsing via SendGrid or IMAP

---

## 5. SMS & WhatsApp (Twilio Integration)

**Path:** Sidebar > SMS & WhatsApp

### 5.1 Setting Up Twilio

#### Step 1: Get Twilio Credentials
1. Sign up at [twilio.com](https://www.twilio.com)
2. From your Twilio Console, copy:
   - **Account SID**
   - **Auth Token**
   - **Phone Number** (buy one from Twilio)

#### Step 2: Configure .env
Open the `.env` file and update:
```
TWILIO_SID=AC_your_actual_account_sid
TWILIO_AUTH_TOKEN=your_actual_auth_token
TWILIO_PHONE_NUMBER=+1your_twilio_number
TWILIO_WHATSAPP_NUMBER=+14155238886
```

#### Step 3: Add Twilio Numbers in CRM
1. Go to **SMS & WhatsApp > Twilio Numbers**
2. Click **"Add Number"**
3. Fill in:
   - **Label** - Friendly name (e.g., "Sales Team", "Support Line")
   - **Phone Number** - Your Twilio number (e.g., +12025551234)
   - **Twilio Account SID** - Leave empty to use default from .env, or enter a different SID for this number
   - **Twilio Auth Token** - Leave empty to use default, or enter a custom token
   - **WhatsApp Enabled** - Check if this number supports WhatsApp
4. Click **Save Number**

You can add **unlimited Twilio numbers**. Each can use the default credentials or have its own.

### 5.2 Sending Messages

#### Send to One Person
1. Go to **SMS & WhatsApp > Messages**
2. Click **"Compose Message"**
3. Select:
   - **From Number** - Choose which Twilio number to send from (or use default)
   - **Channel** - SMS or WhatsApp
   - **To** - Enter phone number (e.g., +923001234567)
   - **Message** - Type your message (max 1600 chars)
4. Click **Send Message**

#### Send to Multiple People (Bulk)
1. Click **"Compose Message"**
2. In the **To** field, enter multiple numbers separated by commas:
   ```
   +923001234567, +923009876543, +12025551234
   ```
3. The recipient count shows at the bottom
4. Each person receives the message individually
5. Each message is tracked separately in history

#### Send from Different Twilio Numbers
1. In the **From Number** dropdown, you'll see all your active Twilio numbers
2. Select the one you want to send from
3. If a number has `[WhatsApp]` tag, it supports WhatsApp messaging
4. Use "Default (.env)" to send from the number configured in `.env`

### 5.3 Receiving Messages

#### Set Up Inbound Webhook
1. In your Twilio Console, go to your phone number settings
2. Under **"A Message Comes In"**, set the webhook URL to:
   ```
   https://your-domain.com/admin/sms/inbound-webhook
   ```
   (Method: HTTP POST)
3. For local testing, use [ngrok](https://ngrok.com) to expose your localhost:
   ```bash
   ngrok http 8000
   ```
   Then use the ngrok URL as your webhook

#### How Inbound Messages Work
- When someone texts your Twilio number, the message appears in your CRM
- The system automatically matches the sender's phone to a Contact (Person)
- Messages are tagged as **Inbound** with status **Received**
- The system identifies which of your Twilio numbers received the message

### 5.4 Conversation History

#### View Message History
- **SMS & WhatsApp > Messages** shows all messages in a DataGrid
- Filter by: Direction, Channel, Status, Date
- Search by: Phone number, message body, contact name
- Sort by any column

#### View Conversation with a Contact
1. In the Messages DataGrid, click on a **contact name** (blue link)
2. Opens a **chat-style conversation view** showing:
   - All messages (sent and received) in chronological order
   - **Outbound messages** - Right side, blue bubbles
   - **Inbound messages** - Left side, gray bubbles
   - Each bubble shows: timestamp, channel (SMS/WhatsApp), status, which Twilio number was used
   - Failed messages show error details in red

#### Reply from Conversation
1. At the bottom of the conversation view:
   - Select **From Number** (which Twilio number to reply from)
   - Select **Channel** (SMS or WhatsApp)
   - Type your message
   - Press **Enter** or click **Send**
2. The conversation refreshes with your new message

### 5.5 Managing Twilio Numbers

**Path:** SMS & WhatsApp > Twilio Numbers

| Action | How |
|--------|-----|
| Add Number | Click "Add Number", fill form, save |
| Edit Number | Click edit icon on the number row |
| Deactivate | Edit > Uncheck "Active" checkbox |
| Delete | Click delete icon on the number row |
| View Message Count | "Messages" column shows how many messages sent/received |

### 5.6 SMS Stats on Dashboard
The dashboard shows an **SMS & WhatsApp Stats** widget with:
- Total Sent / Received / Failed counts
- Today's sent and received counts
- Quick link to the full SMS dashboard

---

## 6. Activities

**Path:** Sidebar > Activities

### Activity Types
| Type | Description |
|------|-------------|
| Call | Phone call records |
| Meeting | Meeting schedules |
| Lunch | Lunch meeting records |
| Email | Email activities |
| Note | Text notes |
| File | File attachments |

### How to Create an Activity
1. Click **"Create Activity"** (or create from a Lead/Contact)
2. Fill in:
   - **Type** - Call, Meeting, Lunch, etc.
   - **Title** - Activity title
   - **Schedule From/To** - Date and time range
   - **Location** - Where (optional)
   - **Description** - Notes
   - **Participants** - People involved
3. Click **Save**

### Activity Features
- Mark activities as **Done**
- Filter by type, date, status
- Activities auto-linked to Leads and Contacts
- Mass update/delete via checkboxes

---

## 7. Contacts

**Path:** Sidebar > Contacts

### 7.1 Persons (Contacts)
- **Create Person**: Name, email(s), phone number(s), organization, job title
- **View Person**: See activities, emails, SMS history, linked leads
- **Tag**: Add color-coded tags
- **Search**: Quick search across all persons

### 7.2 Organizations (Companies)
- **Create Organization**: Company name, address, contacts
- **View Organization**: See linked persons, activities
- **Tag**: Add color-coded tags

---

## 8. Products

**Path:** Sidebar > Products

### How to Create a Product
1. Click **"Create Product"**
2. Fill in: Name, SKU, Description, Price, Quantity
3. Click **Save**

### Product Features
- Track inventory per warehouse
- Link products to leads and quotes
- Tag products
- Activity tracking per product

---

## 9. Settings

**Path:** Sidebar > Settings

### User Management
| Section | Description |
|---------|-------------|
| Groups | Create user groups (Sales, Support, etc.) |
| Roles | Define roles with specific permissions (ACL) |
| Users | Add/manage admin users |

### Lead Configuration
| Section | Description |
|---------|-------------|
| Pipelines | Create sales pipelines with stages |
| Sources | Define lead sources (Web, Referral, etc.) |
| Types | Define lead types (New, Existing, etc.) |

### Inventory
| Section | Description |
|---------|-------------|
| Warehouses | Manage warehouse locations |

### Automation
| Section | Description |
|---------|-------------|
| Attributes | Custom fields for entities |
| Email Templates | Reusable email templates |
| Events | Marketing events |
| Campaigns | Email marketing campaigns |
| Webhooks | External system integrations |
| Workflows | Automated actions on triggers |
| Data Transfer | Bulk import from CSV/Excel |

### Other Settings
| Section | Description |
|---------|-------------|
| Tags | Manage color-coded tags |

---

## 10. Configuration

**Path:** Sidebar > Configuration

Global system settings including:
- General settings (locale, timezone, currency)
- Email settings (SMTP configuration)

---

## Twilio Setup Checklist

- [ ] Create Twilio account at twilio.com
- [ ] Buy a phone number from Twilio
- [ ] Copy Account SID and Auth Token
- [ ] Update `.env` with Twilio credentials
- [ ] Add Twilio number(s) in CRM (SMS & WhatsApp > Twilio Numbers)
- [ ] Enable WhatsApp on numbers that support it
- [ ] Configure inbound webhook URL in Twilio Console
- [ ] Test sending an SMS from CRM
- [ ] Test sending a WhatsApp message from CRM
- [ ] Test receiving an inbound message
- [ ] Verify conversation history shows correctly

---

## API Endpoints Reference

### SMS Endpoints
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/admin/sms` | Messages list (DataGrid) |
| POST | `/admin/sms/send` | Send SMS/WhatsApp |
| GET | `/admin/sms/stats` | Get message statistics |
| GET | `/admin/sms/conversation/{personId}` | Conversation with contact |
| GET | `/admin/sms/message/{id}` | View single message |
| DELETE | `/admin/sms/message/{id}` | Delete message |
| POST | `/admin/sms/inbound-webhook` | Receive inbound (no auth) |

### Twilio Numbers Endpoints
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/admin/sms/numbers` | List all numbers |
| POST | `/admin/sms/numbers` | Add new number |
| GET | `/admin/sms/numbers/active` | Get active numbers |
| GET | `/admin/sms/numbers/{id}` | Get number details |
| PUT | `/admin/sms/numbers/{id}` | Update number |
| DELETE | `/admin/sms/numbers/{id}` | Delete number |

---

## Database Tables

### sms_messages
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| from | varchar | Sender phone number |
| to | varchar | Recipient phone number |
| body | text | Message content |
| direction | enum | outbound / inbound |
| status | varchar | queued, sent, delivered, received, failed |
| channel | enum | sms / whatsapp |
| twilio_sid | varchar | Twilio message SID |
| twilio_number_id | bigint | Which Twilio number used |
| person_id | int | Linked contact |
| lead_id | int | Linked lead |
| user_id | int | CRM user who sent |
| error_message | varchar | Error details if failed |
| created_at | timestamp | When created |

### twilio_numbers
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| label | varchar | Friendly name |
| phone_number | varchar | E.164 format number |
| twilio_sid | varchar | Optional custom Account SID |
| twilio_token | varchar | Optional custom Auth Token |
| is_whatsapp | boolean | WhatsApp enabled |
| is_active | boolean | Active/inactive |
| created_at | timestamp | When added |

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Class NumberFormatter not found" | Enable `extension=intl` in `F:/xampp/php/php.ini` |
| PHP version error | Platform set to 8.3.0 in composer.json config |
| Messages show "failed" | Check Twilio SID and Auth Token in `.env` |
| Inbound messages not appearing | Verify webhook URL is set in Twilio Console |
| WhatsApp not sending | Ensure number has WhatsApp enabled in Twilio and CRM |
| Cannot add Twilio number | Check phone number format (must include country code, e.g., +1...) |
