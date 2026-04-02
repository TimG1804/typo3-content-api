-- API test fixture: tt_content
--
-- UIDs start at 5100 to avoid conflicts with Introduction Package content.
-- Use INSERT IGNORE so the script is idempotent (safe to re-run).
--
-- Elements on pid=5001 (API Test Home):
--   uid=5100  text element (default language)
--   uid=5101  textmedia element (default language)
--   uid=5110  text element, German translation of uid=5100
--
-- Elements on pid=5002 (API Test Media):
--   uid=5120  textmedia element used to test media-field assertions

-- REPLACE INTO updates existing rows by uid, ensuring all fields are correct on re-runs.
REPLACE INTO `tt_content`
    (`uid`, `pid`, `CType`, `colPos`, `sorting`,
     `sys_language_uid`, `l18n_parent`,
     `header`, `bodytext`,
     `imageorient`, `imagecols`,
     `hidden`, `deleted`)
VALUES
    -- text element on "API Test Home", default language
    (5100, 5001, 'text',      0, 256,  0, 0,
     'Test Headline',     '<p>Test content</p>',       0, 1, 0, 0),

    -- textmedia element on "API Test Home", default language
    (5101, 5001, 'textmedia', 0, 512,  0, 0,
     'Textmedia Headline','<p>Textmedia content</p>',  0, 1, 0, 0),

    -- German translation of uid=5100, on "API Test Home"
    (5110, 5001, 'text',      0, 256,  1, 5100,
     'Test Ueberschrift', '<p>Testinhalt</p>',         0, 1, 0, 0),

    -- textmedia element on "API Test Media", default language
    (5120, 5002, 'textmedia', 0, 256,  0, 0,
     'Media Page Headline','<p>Media page content</p>',0, 1, 0, 0)
;
