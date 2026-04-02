-- API test fixture: pages
--
-- UIDs start at 5001 to avoid conflicts with Introduction Package content.
-- Use INSERT IGNORE so the script is idempotent (safe to re-run).
--
-- All pages use pid=1 (the TYPO3 root page created by `typo3 setup`).
-- This keeps them inside the existing site (e.g. "main") so TYPO3 site
-- routing resolves them correctly — no separate site config needed.
--
-- Test pages (pid=1): visible pages used in API contract tests.
-- Hidden page (pid=1, hidden=1): used to verify 404 for hidden pages.
-- Language overlay (sys_language_uid=1): German translation of uid=5001.

-- Remove stale root page from previous fixture versions (uid=5000, pid=0)
DELETE FROM `pages` WHERE `uid` = 5000;

-- REPLACE INTO updates existing rows by uid (DELETE + INSERT), ensuring
-- pid and all other fields are always in the correct state on re-runs.
REPLACE INTO `pages`
    (`uid`, `pid`, `title`, `doktype`, `slug`, `hidden`, `deleted`,
     `sys_language_uid`, `l10n_parent`, `tstamp`, `crdate`, `sorting`)
VALUES
    -- Visible test page with text + textmedia content elements (see tt_content.sql)
    (5001, 1, 'API Test Home',    1, '/api-test-home',  0, 0, 0, 0, 0, 0, 9000),

    -- Visible test page for textmedia-specific assertions
    (5002, 1, 'API Test Media',   1, '/api-test-media', 0, 0, 0, 0, 0, 0, 9256),

    -- Hidden page — must return 404 via the API
    (5003, 1, 'API Test Hidden',  1, '/api-test-hidden',1, 0, 0, 0, 0, 0, 9512),

    -- German translation of uid=5001
    (5010, 1, 'API Test Home DE', 1, '/api-test-home',  0, 0, 1, 5001, 0, 0, 9000)
;
