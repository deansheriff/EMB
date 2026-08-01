UPDATE page_content
SET image_path = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1200&q=85',
    image_alt = 'A Black female health professional in a bright clinical environment'
WHERE page_key = 'about'
  AND section_key = 'intro'
  AND (image_path IS NULL OR image_path = '');

UPDATE page_content
SET image_path = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85',
    image_alt = 'A diverse group sharing a supportive conversation'
WHERE page_key = 'fiyff'
  AND section_key = 'mission'
  AND (image_path IS NULL OR image_path = '');

-- Keep this marker insert last: the container entrypoint checks for this row before
-- running the migration, so an interrupted migration will be retried safely.
INSERT INTO page_content
  (page_key, section_key, eyebrow, heading, content, image_path, image_alt, link_label, link_url, status)
VALUES
  ('about', 'guide', 'Meet your guide', 'Zubaida’s dream-chasing philosophy',
   '<p>With a B.Sc. in Microbiology and advanced post-graduate certification in Assisted Reproductive Technology from IMSA, Zubaida has spent nearly four years working in a leading fertility-clinic environment in Abuja.</p><p>Emb Chronicles grew from a simple conviction: people deserve the context, language, and confidence to participate more fully in decisions about their bodies, treatment, and careers.</p>',
   'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=85',
   'A mentor speaking with people around a table', 'Book a conversation', '/appointment', 'published')
ON DUPLICATE KEY UPDATE page_key = VALUES(page_key);
