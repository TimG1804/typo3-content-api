-- API test fixture: pages
--
-- UIDs start at 5001 to avoid conflicts with any pre-installed content.
-- REPLACE INTO updates existing rows by uid — safe to re-run.
--
-- All pages use pid=1 (the TYPO3 root page created by `typo3 setup`).
-- This keeps them inside the existing site so TYPO3 site routing resolves
-- them correctly without a separate site config.
--
-- Page overview:
--   5001  API Test Home   — visible, EN; has text + textmedia content
--   5002  API Test Media  — visible, EN; no_index=1 for SEO flag testing
--   5003  API Test Hidden — hidden; must return 404 via the API
--   5010  API Test Home DE — German translation of 5001 (sys_language_uid=1)

-- Remove stale root page from previous fixture versions (uid=5000, pid=0)
DELETE FROM `pages` WHERE `uid` = 5000;

REPLACE INTO `pages`
    (`uid`, `pid`, `title`, `nav_title`, `doktype`, `slug`,
     `hidden`, `deleted`,
     `sys_language_uid`, `l10n_parent`,
     `tstamp`, `crdate`, `sorting`,
     `lastUpdated`,
     `description`,
     `seo_title`, `no_index`, `no_follow`,
     `og_title`, `og_description`,
     `canonical_link`,
     `fe_group`, `extendToSubpages`,
     `starttime`, `endtime`)
VALUES
    -- Visible test page with text + textmedia content (see tt_content.sql).
    -- Has explicit SEO data for SeoDto + AccessDto contract tests.
    (5001, 1, 'API Test Home', 'Home Nav', 1, '/api-test-home',
     0, 0,
     0, 0,
     0, 0, 9000,
     0,
     'Meta description for API Test Home',
     'API Test Home – SEO Title', 0, 0,
     'OG Title for Home', 'OG description for Home',
     '',
     '', 0,
     0, 0),

    -- Visible test page for textmedia and image assertions.
    -- no_index=1 to verify that the robots string in SeoDto reflects the flag.
    (5002, 1, 'API Test Media', '', 1, '/api-test-media',
     0, 0,
     0, 0,
     0, 0, 9256,
     0,
     '',
     '', 1, 0,
     '', '',
     '',
     '', 0,
     0, 0),

    -- Hidden page — must return 404 via the API
    (5003, 1, 'API Test Hidden', '', 1, '/api-test-hidden',
     1, 0,
     0, 0,
     0, 0, 9512,
     0,
     '',
     '', 0, 0,
     '', '',
     '',
     '', 0,
     0, 0),

    -- German translation of uid=5001
    (5010, 1, 'API Test Home DE', 'Home Nav DE', 1, '/api-test-home',
     0, 0,
     1, 5001,
     0, 0, 9000,
     0,
     'Deutsche Beschreibung',
     '', 0, 0,
     '', '',
     '',
     '', 0,
     0, 0)
;
