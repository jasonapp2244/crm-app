import { test, expect } from "../setup";
import fs from "fs";
import { generateName, getRandomDateTime, generateDescription, generateDate, generateEmail, generatePhoneNumber,generateEmailSubject } from '../utils/faker';

async function clearProductsIfPresent(page) {
  const deleteIcons = page.locator('#products i.icon-delete');

  // Leads create/edit screens may auto-add an empty product row; remove it to avoid required-field validation.
  for (let i = 0; i < 10; i++) {
    const count = await deleteIcons.count();

    if (!count) {
      break;
    }

    await deleteIcons.first().click();
  }
}

async function generateLead(adminPage) {
    /**
     * Go to the lead listing page.
     */
    await adminPage.goto("admin/leads");
    await adminPage.getByRole('link', { name: 'Create Lead' }).click();

    /**
     * Fill the lead form.
     */
    const leadTitle = `${generateName()}-${Date.now()}`;
    const leadDescription = generateDescription();
    const leadDate = generateDate();
    const leadEmail = generateEmail();
    const leadPhoneNumber = generatePhoneNumber();

    await adminPage.fill('input[name="title"]', leadTitle);
    await adminPage.fill('textarea[name="description"]', leadDescription);
    await adminPage.locator('select[name="lead_source_id"]').selectOption("1");
    await adminPage.fill('input[name="expected_close_date"]', leadDate);
    await adminPage.locator('select[name="lead_type_id"]').selectOption("1");
    await adminPage.locator('select[name="user_id"]').selectOption("1");
    await adminPage.fill('input[name="lead_value"]', '1000');

    /**
     * Add a new person.
     */
    await adminPage.locator('div').filter({ hasText: /^Click to Add$/ }).nth(1).click();
    await adminPage.getByRole('textbox', { name: 'Search...' }).fill(leadTitle);
    await adminPage.getByText('Add as New').click();

    await adminPage.fill('input[name="person[emails][0][value]"]', leadEmail);
    await adminPage.fill('input[name="person[contact_numbers][0][value]"]', leadPhoneNumber);

    /**
     * Associate an organization.
     */
    await adminPage.locator('div').filter({ hasText: /^Click to add$/ }).nth(2).click();
    await adminPage.getByRole('textbox', { name: 'Search...' }).fill(leadTitle);
    await adminPage.getByText('Add as New').click();

    /**
     * Save the lead.
     */
    await clearProductsIfPresent(adminPage);

    await adminPage.getByRole('button', { name: 'Save' }).click();

    const leadCreatedMessage = adminPage.locator('#app p', {
      hasText: 'Lead created successfully.',
    }).first();

    try {
      await expect(leadCreatedMessage).toBeVisible({ timeout: 5000 });
    } catch {
      const productRequiredMessage = adminPage.getByText('The Product Name field is required');
      const hasProductValidationError = await productRequiredMessage
        .isVisible()
        .catch(() => false);

      if (hasProductValidationError) {
        await clearProductsIfPresent(adminPage);
        await adminPage.getByRole('button', { name: 'Save' }).click();
      }
    }

    /**
     * Assertion to confirm lead creation.
     */
    await expect(leadCreatedMessage).toBeVisible();

    return { leadTitle, leadDescription, leadDate, leadEmail, leadPhoneNumber };
} 

function generateFile(fileName, content) {
    fs.writeFileSync(fileName, content);
    return fileName;
}

async function openLeadByTitle(adminPage, leadTitle) {
  await adminPage.goto("admin/leads");
  const searchInput = adminPage.getByRole("textbox", { name: "Search by Title" });
  await searchInput.fill(leadTitle);
  await searchInput.press("Enter");

  const leadLink = adminPage
    .locator('a[href*="/admin/leads/view/"]')
    .filter({ hasText: leadTitle })
    .first();

  await expect(leadLink).toBeVisible();
  await leadLink.click();
}

test.describe("lead management", () => {
    
    test("should create a new lead", async ({ adminPage }) => {
    /**
     * Create a new lead.
     */
        await generateLead(adminPage);
    });

    test("should able to update lead", async ({ adminPage }) => {
       /**
        * Create a new lead.
        */
       const lead = await generateLead(adminPage);

       /**
        * Update the lead.
        */
      await openLeadByTitle(adminPage, lead.leadTitle);
       const page1Promise = adminPage.waitForEvent('popup');
       await adminPage.getByRole('link', { name: '' }).first().click();
       const page1 = await page1Promise;
       await page1.fill('textarea[name="description"]', generateDescription());
       await page1.fill('input[name="title"]', generateName());
       await page1.getByLabel('Source').selectOption('3');
       await page1.fill('input[name="lead_value"]', '30000');
         await clearProductsIfPresent(page1);
       await page1.getByRole('button', { name: 'Save' }).click();

         const productRequiredMessage = page1.getByText('The Product Name field is required');
         const hasProductValidationError = await productRequiredMessage
           .isVisible()
           .catch(() => false);

         if (hasProductValidationError) {
           await clearProductsIfPresent(page1);
           await page1.getByRole('button', { name: 'Save' }).click();
         }

       await expect(page1.locator('#app')).toContainText('Leads updated successfully.');
    });

    test("should able to delete lead", async ({ adminPage }) => {
       /**
        * Create a new lead.
        */
       const lead = await generateLead(adminPage);

       /**
        * Delete the lead.
        */
       await adminPage.getByRole('link', { name: '' }).click();
       await adminPage.locator('div:nth-child(4) > .flex > span:nth-child(2)').click();
       await adminPage.getByRole('button', { name: 'Agree', exact: true }).click();
       await expect(adminPage.getByText('Success', { exact: true })).toBeVisible();
       await expect(adminPage.locator('#app')).toContainText('Lead deleted successfully.');
      });

    test("should sent a mail", async ({ adminPage }) => {
       /**
        * Create a new lead.
        */
       const lead = await generateLead(adminPage);

       /**
        * fill mail detail.
        */
       await openLeadByTitle(adminPage, lead.leadTitle);
       await adminPage.getByRole('button', { name: ' Mail' }).click();
       await adminPage.fill('input[name="temp-reply_to"]', generateEmail());
       await adminPage.fill('input[name="subject"]', generateEmailSubject());
       await adminPage.fill('textarea[name="reply"]', generateDescription());
   
       /**
        * Sending mail and closing the modal.
        */
       await adminPage.getByRole('button', { name: 'Send' }).click();
   
       await expect(adminPage.getByText('Email sent successfully.')).toBeVisible();
   });

   test("should able to upload file in  lead", async ({ adminPage }) => {
    /**
     * Create a new lead.
     */
    const lead = await generateLead(adminPage);

    /**
     * fill the file detail or upload a file.
     */
    await openLeadByTitle(adminPage, lead.leadTitle);
    await adminPage.getByRole('button', { name: ' File' }).click();
    await adminPage.locator('input[name="title"]').fill(lead.leadTitle);
    await adminPage.locator('textarea[name="comment"]').fill(generateDescription());
    await adminPage.locator('input[name="name"]').fill(generateName());
    await adminPage.locator('#file').setInputFiles(generateFile('example.txt', 'Hello, this is a generated file!'));
    await adminPage.getByRole('button', { name: 'Save File' }).click();
   });

   test("should able to write a note in lead", async ({ adminPage }) => {
    /**
     * Create a new lead.
     */
    const lead = await generateLead(adminPage);

    /**
     * write a notes 
     */
    await openLeadByTitle(adminPage, lead.leadTitle);
    await adminPage.getByRole('button', { name: ' Note' }).click();
    await adminPage.locator('textarea[name="comment"]').fill(generateDescription());
    await adminPage.getByRole('button', { name: 'Save Note' }).click();
   });

   test("should able to add call activity in lead", async ({ adminPage }) => {
     /**
     * Create a new lead.
     */
     const lead = await generateLead(adminPage);

     /**
      * write a call activity detail 
      */
     await openLeadByTitle(adminPage, lead.leadTitle);
     await adminPage.getByRole('button', { name: ' Activity' }).click();
     await adminPage.getByRole('heading', { name: 'Add Activity - Call ' }).locator('span').click();
     await adminPage.getByText('Call', { exact: true }).click();
     await adminPage.locator('input[name="title"]').fill(lead.leadTitle);
     await adminPage.locator('textarea[name="comment"]').fill(generateDescription());
     await adminPage.locator('input[name="schedule_from"]').click();
     await adminPage.locator('input[name="schedule_from"]').fill(getRandomDateTime());
     await adminPage.locator('input[name="schedule_to"]').click();
     await adminPage.locator('input[name="schedule_to"]').fill(getRandomDateTime());
     await adminPage.locator('input[name="location"]').fill('call');
     await adminPage.getByRole('button', { name: 'Save Activity' }).click();

   });

   test("should able to add meeting activity in lead", async ({ adminPage }) => {
    /**
    * Create a new lead.
    */
    const lead = await generateLead(adminPage);

    /**
     * write a call activity detail 
     */
    await openLeadByTitle(adminPage, lead.leadTitle);
    await adminPage.getByRole('button', { name: ' Activity' }).click();
    await adminPage.getByRole('heading', { name: 'Add Activity' }).locator('span').click();
    await adminPage.getByText('Meeting', { exact: true }).click();
    await adminPage.locator('input[name="title"]').fill(lead.leadTitle);
    await adminPage.locator('textarea[name="comment"]').fill(generateDescription());
    await adminPage.locator('input[name="schedule_from"]').click();
    await adminPage.locator('input[name="schedule_from"]').fill(getRandomDateTime());
    await adminPage.locator('input[name="schedule_to"]').click();
    await adminPage.locator('input[name="schedule_to"]').fill(getRandomDateTime());
    await adminPage.locator('input[name="location"]').fill('Google meet');
    await adminPage.getByRole('button', { name: 'Save Activity' }).click();
  });

  test("should able to add lunch activity in lead", async ({ adminPage }) => {
    /**
    * Create a new lead.
    */
    const lead = await generateLead(adminPage);

    /**
     * write a call activity detail 
     */
    await openLeadByTitle(adminPage, lead.leadTitle);
    await adminPage.getByRole('button', { name: ' Activity' }).click();
    await adminPage.getByRole('heading', { name: 'Add Activity' }).locator('span').click();
    await adminPage.getByText('Lunch', { exact: true }).click();
    await adminPage.locator('input[name="title"]').fill(lead.leadTitle);
    await adminPage.locator('textarea[name="comment"]').fill(generateDescription());
    await adminPage.locator('input[name="schedule_from"]').click();
    await adminPage.locator('input[name="schedule_from"]').fill(getRandomDateTime());
    await adminPage.locator('input[name="schedule_to"]').click();
    await adminPage.locator('input[name="schedule_to"]').fill(getRandomDateTime());
    await adminPage.locator('input[name="location"]').fill('Restraunt');
    await adminPage.getByRole('button', { name: 'Save Activity' }).click();
  });
});