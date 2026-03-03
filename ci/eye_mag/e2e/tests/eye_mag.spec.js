const { test, expect } = require('@playwright/test');

const env = {
  baseUrl: process.env.EYE_MAG_BASE_URL || '',
  username: process.env.EYE_MAG_USERNAME || '',
  password: process.env.EYE_MAG_PASSWORD || '',
  pid: process.env.EYE_MAG_PID || '',
  encounter: process.env.EYE_MAG_ENCOUNTER || '',
  formId: process.env.EYE_MAG_FORM_ID || ''
};

const missingEnv = Object.entries(env)
  .filter(([, value]) => !value)
  .map(([key]) => key);

test.skip(
  missingEnv.length > 0,
  `Missing required E2E env vars: ${missingEnv.join(', ')}`
);

function buildUrl(path) {
  const base = env.baseUrl.replace(/\/+$/, '');
  return `${base}${path}`;
}

async function login(page) {
  await page.goto(buildUrl('/interface/login/login.php?site=default'), { waitUntil: 'domcontentloaded' });

  const userInput = page.locator("input[name='authUser'], input[name='authuser']").first();
  await userInput.waitFor({ state: 'visible' });
  await userInput.fill(env.username);

  const passInput = page.locator("input[name='clearPass'], input[type='password']").first();
  await passInput.fill(env.password);

  const submitButton = page.locator("button[type='submit'], input[type='submit']").first();
  await Promise.all([
    page.waitForLoadState('networkidle'),
    submitButton.click()
  ]);
}

test.describe.serial('EyeMag automation checks', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Medication opens Eye Med unchecked', async ({ page }) => {
    const issueUrl = buildUrl(
      `/interface/forms/eye_mag/a_issue.php?pid=${encodeURIComponent(env.pid)}&encounter=${encodeURIComponent(env.encounter)}&form_id=${encodeURIComponent(env.formId)}&issue=0&thistype=medication`
    );
    await page.goto(issueUrl, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#form_eye_subtype');

    await page.evaluate(() => {
      if (typeof newtype === 'function') {
        newtype('Medication');
      }
    });

    await expect(page.locator('#form_eye_subtype')).not.toBeChecked();
  });

  test('Diagnosis code normalization avoids ICD duplicates', async ({ page }) => {
    const viewUrl = buildUrl(
      `/interface/forms/eye_mag/view.php?id=${encodeURIComponent(env.formId)}&pid=${encodeURIComponent(env.pid)}&encounter=${encodeURIComponent(env.encounter)}`
    );
    await page.goto(viewUrl, { waitUntil: 'domcontentloaded' });

    const normalizedCode = await page.evaluate(() => {
      if (typeof normalizeIMPPLANCodeList !== 'function') {
        return null;
      }
      return normalizeIMPPLANCodeList('Z96.1, Z96.1, H25.9').join(', ');
    });
    expect(normalizedCode).toBe('Z96.1, H25.9');

    const renderedCode = await page.evaluate(() => {
      if (typeof build_IMPPLAN !== 'function') {
        return null;
      }
      build_IMPPLAN([{
        title: 'Pseudofaquia',
        code: 'Z96.1, Z96.1',
        codetype: 'ICD10',
        codedesc: 'Pseudofaquia',
        codetext: 'ICD10:Z96.1 (Pseudofaquia)',
        plan: '',
        PMSFH_link: ''
      }], '1');
      const codeNode = document.getElementById('CODE_0');
      return codeNode ? codeNode.textContent.trim() : null;
    });
    expect(renderedCode).toBe('Z96.1');
  });

  test('PLAN starts empty with placeholder and template hint', async ({ page }) => {
    const viewUrl = buildUrl(
      `/interface/forms/eye_mag/view.php?id=${encodeURIComponent(env.formId)}&pid=${encodeURIComponent(env.pid)}&encounter=${encodeURIComponent(env.encounter)}`
    );
    await page.goto(viewUrl, { waitUntil: 'domcontentloaded' });

    const planState = await page.evaluate(() => {
      if (typeof build_IMPPLAN !== 'function') {
        return null;
      }
      build_IMPPLAN([{
        title: 'Glaucoma',
        code: 'H40.9',
        codetype: 'ICD10',
        codedesc: 'Glaucoma',
        codetext: 'ICD10:H40.9 (Glaucoma)',
        plan: '',
        PMSFH_link: ''
      }], '1');

      const plan = document.getElementById('PLAN_0');
      const hint = document.getElementById('PLAN_HINT_0');
      return {
        value: plan ? plan.value : null,
        placeholder: plan ? (plan.getAttribute('placeholder') || '') : '',
        hintText: hint ? hint.textContent : ''
      };
    });

    expect(planState).not.toBeNull();
    expect(planState.value).toBe('');
    expect(planState.placeholder).toContain('Puedes escribir observaciones y plan para este diagnostico');
    expect(planState.hintText).toContain('Suggested plan');
  });
});
