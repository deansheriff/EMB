USE emb_chronicles;

INSERT INTO admins (name, email, password_hash, role)
VALUES ('EMB Administrator', 'admin@embchronicles.com', '$2y$10$A502e1h/u/FBxhkUjpb2zeu/j.rkWT.gUGUL0.lfhSdWYhkK2OPKq', 'admin')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO roles (name, slug, description, is_system, is_super) VALUES
('Super Administrator', 'super-administrator', 'Unrestricted access to every administration feature, including users and roles.', 1, 1),
('Content Manager', 'content-manager', 'Manages public content, services, events, hero slides, and testimonials.', 1, 0),
('Client Services', 'client-services', 'Manages enquiries, appointments, payment records, and email delivery history.', 1, 0),
('Grant Reviewer', 'grant-reviewer', 'Reviews and manages FIYFF grant applications.', 1, 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_system = VALUES(is_system), is_super = VALUES(is_super);

INSERT INTO permissions (slug, name, group_name, description) VALUES
('dashboard.view', 'View dashboard', 'General', 'Open the admin dashboard and see permitted summaries.'),
('events.manage', 'Manage events', 'Content', 'Create, update, publish, and delete events.'),
('services.manage', 'Manage services', 'Content', 'Create, update, publish, and delete services.'),
('heroes.manage', 'Manage hero slides', 'Content', 'Manage homepage hero slides and media.'),
('testimonials.manage', 'Manage testimonials', 'Content', 'Create, update, show, and hide testimonials.'),
('content.manage', 'Manage page content', 'Content', 'Edit managed page sections and supporting media.'),
('grants.manage', 'Manage grant forms and applications', 'Programs', 'Create grant forms, review applications, access protected documents, and assign reviewers.'),
('contacts.manage', 'Manage contact submissions', 'Client Services', 'Read, archive, and delete contact submissions.'),
('appointments.manage', 'Manage appointments', 'Client Services', 'Manage bookings, payment history, scheduling, and client updates.'),
('email_log.view', 'View email log', 'Client Services', 'Review transactional email delivery history and failures.'),
('settings.manage', 'Manage site settings', 'Configuration', 'Manage branding, SMTP, Paystack, and global settings.'),
('users.manage', 'Manage administrators', 'Access Control', 'Create, update, activate, and deactivate admin users.'),
('roles.manage', 'Manage roles', 'Access Control', 'Create roles and assign permissions within the current administrator’s authority.')
ON DUPLICATE KEY UPDATE name = VALUES(name), group_name = VALUES(group_name), description = VALUES(description);

INSERT IGNORE INTO admin_roles (admin_id, role_id)
SELECT a.id, r.id FROM admins a JOIN roles r ON r.slug = 'super-administrator'
WHERE a.email = 'admin@embchronicles.com';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'content-manager' AND p.slug IN
('dashboard.view','events.manage','services.manage','heroes.manage','testimonials.manage','content.manage');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'client-services' AND p.slug IN
('dashboard.view','contacts.manage','appointments.manage','email_log.view');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'grant-reviewer' AND p.slug IN ('dashboard.view','grants.manage');

INSERT INTO site_settings (`key`, `value`, `type`) VALUES
('site_name', 'Emb Chronicles', 'text'),
('tagline', 'Fertility Education', 'text'),
('phone', '+234 707 587 0272', 'text'),
('whatsapp', 'https://wa.me/2347075870272', 'url'),
('email', 'info@embchronicles.com', 'email'),
('address', 'Suite 05 Femi Kila Street, Lento Aluminum, Lifecamp, Abuja', 'text'),
('opening_hours', 'Monday–Friday, 09:00–17:00', 'text'),
('instagram', 'https://instagram.com/emb_chronicles', 'url'),
('tiktok', 'https://tiktok.com/@emb_chronicles', 'url'),
('footer_blurb', 'Fertility education that is accessible, compassionate, and science-driven for everyone who needs it.', 'textarea'),
('stats_members', '4000', 'number'),
('stats_families', '0', 'number'),
('logo_path', '', 'image'),
('default_meta_title', 'Emb Chronicles — Fertility Education', 'text'),
('default_meta_description', 'Clear, compassionate fertility education, consultation, community support, and STEM career mentorship.', 'textarea'),
('social_share_image', '', 'image'),
('social_share_image_alt', '', 'text'),
('smtp_enabled', '0', 'boolean'),
('smtp_host', '', 'text'),
('smtp_port', '587', 'number'),
('smtp_encryption', 'tls', 'text'),
('smtp_username', '', 'text'),
('smtp_password', '', 'secret'),
('smtp_from_email', 'info@embchronicles.com', 'email'),
('smtp_from_name', 'Emb Chronicles', 'text'),
('smtp_reply_to', 'info@embchronicles.com', 'email'),
('smtp_admin_email', 'info@embchronicles.com', 'email'),
('email_confirmations_enabled', '1', 'boolean'),
('paystack_enabled', '0', 'boolean'),
('paystack_public_key', '', 'text'),
('paystack_secret_key', '', 'secret'),
('paystack_currency', 'NGN', 'text'),
('appointment_fee', '0.00', 'money')
ON DUPLICATE KEY UPDATE
`value` = IF(
  site_settings.`key` IN (
    'smtp_enabled','smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_password',
    'smtp_from_email','smtp_from_name','smtp_reply_to','smtp_admin_email','email_confirmations_enabled',
    'paystack_enabled','paystack_public_key','paystack_secret_key','paystack_currency','appointment_fee'
  ),
  site_settings.`value`,
  VALUES(`value`)
),
`type` = VALUES(`type`);

INSERT INTO hero_slides
(image_path, image_alt, headline, subheading, cta_label, cta_link, secondary_label, secondary_link, sort_order, is_active)
VALUES
('/assets/images/hero-consultation.webp', 'A Nigerian fertility educator in a supportive consultation with a Nigerian couple', 'The safe space between the clinic and the outside world', 'We translate fertility science into clarity, hope, and confident next steps—without judgment or overwhelm.', 'Make an appointment', '/appointment', 'Explore our support', '/services', 1, 1),
('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1800&q=85', 'A healthcare professional reviewing information with a client', 'Fulfilling your dreams of parenthood with clearer decisions', 'Practical fertility education and focused consultation for the questions that matter to your journey.', 'Book a session', '/appointment', 'How we help', '/services', 2, 1),
('https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=1800&q=85', 'A clinical laboratory environment', 'Complex cases, focused solutions', 'Understand your options, prepare better questions, and move through treatment with more confidence.', 'Make an appointment', '/appointment', 'Learn about us', '/about', 3, 1)
ON DUPLICATE KEY UPDATE headline = VALUES(headline);

INSERT INTO services
(title, slug, excerpt, description, cover_image, cover_alt, sort_order, is_pinned, status, seo_title, seo_description)
VALUES
('Consultations and IVF Overview', 'consultations-and-ivf-overview',
 'Personalized sessions focused on understanding test results, embryo grading, treatment timelines, and laboratory processes.',
 '<h2>Make the science easier to navigate</h2><p>Your fertility journey can arrive with unfamiliar terms, rushed appointments, and results that are difficult to place in context. This session creates room to slow down, organise your questions, and understand the science behind the next conversation with your clinical team.</p><h3>What we can explore together</h3><ul><li>IVF and ICSI treatment pathways</li><li>Laboratory reports and embryo grading</li><li>Questions to take into your next clinic visit</li><li>Treatment timelines and decision points</li></ul><p><strong>Please note:</strong> EMB Chronicles provides education and guidance. Sessions do not replace diagnosis or treatment from a licensed medical team.</p>',
 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=85',
 'A professional consultation in a calm healthcare setting', 1, 1, 'published',
 'IVF Overview Consultation | Emb Chronicles',
 'A personal fertility education session covering IVF timelines, test results, embryo grading, and laboratory processes.'),
('Fertility Education & IVF Clarity', 'fertility-education-and-ivf-clarity',
 'Structured education around IVF, ICSI, IUI, genetic screening, donor conception, and surrogacy.',
 '<h2>Clear explanations for complex treatment options</h2><p>Understanding fertility treatment should not feel inaccessible. We explain what common assisted-reproduction options involve, the language you may hear, and the questions worth asking.</p><ul><li>IVF, ICSI, and IUI</li><li>Genetic screening</li><li>Donor conception</li><li>Surrogacy pathways</li></ul>',
 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1200&q=85',
 'A scientist examining a sample in a laboratory', 2, 1, 'published',
 'Fertility Education and IVF Clarity | Emb Chronicles',
 'Plain-language fertility education covering assisted reproduction and treatment options.'),
('Strategic Clinic Guidance', 'strategic-clinic-guidance',
 'Start from stronger questions when choosing a fertility clinic and evaluating the information presented to you.',
 '<h2>Begin your journey on solid ground</h2><p>We help you understand the factors worth considering when choosing a clinic, reviewing success-rate claims, and preparing for an initial consultation.</p>',
 'https://images.unsplash.com/photo-1576765608866-5b51046452be?auto=format&fit=crop&w=1200&q=85',
 'A client and adviser discussing options at a desk', 3, 1, 'published',
 'Strategic Fertility Clinic Guidance | Emb Chronicles',
 'Guidance for evaluating fertility clinics and preparing informed questions.'),
('Clinic Journey Advocacy', 'clinic-journey-advocacy',
 'Identify gaps, clarify rushed explanations, and prepare more productive conversations with your medical team.',
 '<h2>Never be left with avoidable unanswered questions</h2><p>We help you review the parts of your treatment plan that felt rushed or unclear, then organise the questions you want to raise with your clinic.</p>',
 'https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=1200&q=85',
 'A healthcare conversation with notes on a desk', 4, 0, 'published',
 'Clinic Journey Advocacy | Emb Chronicles',
 'Support for clearer, more productive fertility-clinic conversations.'),
('The Safe Space Consultation', 'the-safe-space-consultation',
 'A judgment-free session to discuss fears, options, results, and the questions you have not had space to ask.',
 '<h2>A calm place for your questions</h2><p>We are not only looking at charts. We create a compassionate, informed space for the human part of the fertility journey.</p>',
 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?auto=format&fit=crop&w=1200&q=85',
 'Two women in a calm supportive conversation', 5, 0, 'published',
 'Safe Space Fertility Consultation | Emb Chronicles',
 'A judgment-free fertility education and support session.')
ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), description = VALUES(description);

INSERT INTO events
(title, slug, excerpt, description, event_date, event_end, location_mode, location, event_type, external_link, cover_image, cover_alt, is_featured, status, seo_title, seo_description)
VALUES
('FIYFF Fertility Support Grant', 'fiyff-fertility-support-grant',
 'A ₦500,000 fertility support grant from the Fatima Ibrahim Yakubu Fertility Foundation.',
 '<h2>About the support grant</h2><p>The FIYFF Fertility Support Grant is designed to provide practical financial support to an eligible couple navigating fertility care.</p><h3>Before you apply</h3><ul><li>Read the eligibility requirements carefully.</li><li>Prepare the information requested in the application.</li><li>Submit one complete application before the deadline.</li></ul><p>Meeting the eligibility criteria does not guarantee selection. Every submission is reviewed with care and privacy.</p>',
 '2026-10-15 12:00:00', '2026-11-15 17:00:00', 'hybrid', 'Abuja and online',
 'Grant Program', '/grant-application/fiyff-fertility-support-grant',
 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=85',
 'People joining hands in a supportive community', 1, 'published',
 'FIYFF ₦500,000 Fertility Support Grant',
 'Review eligibility and apply for the FIYFF Fertility Support Grant.'),
('TTC Community Conversation', 'ttc-community-conversation',
 'A guided conversation where science, empathy, and real fertility experiences meet.',
 '<p>Join a moderated community conversation created for honest questions, practical learning, and compassionate support.</p>',
 '2026-09-05 18:00:00', '2026-09-05 19:30:00', 'online', 'Online',
 'Community Event', '', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85',
 'A small group sharing a positive community conversation', 1, 'published',
 'TTC Community Conversation | Emb Chronicles',
 'A guided online conversation for the trying-to-conceive community.'),
('STEM Career Clarity Clinic', 'stem-career-clarity-clinic',
 'A practical career session for life-science graduates exploring Assisted Reproductive Technology.',
 '<p>Bring your career questions and learn how to turn a life-science degree into a clearer roadmap toward ART and related laboratory careers.</p>',
 '2026-09-26 11:00:00', '2026-09-26 13:00:00', 'online', 'Online',
 'Workshop', '', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1200&q=85',
 'Young scientists collaborating in a laboratory', 0, 'published',
 'STEM Career Clarity Clinic | Emb Chronicles',
 'Career direction for life-science graduates interested in ART.'),
('Fertility Education Live Session', 'fertility-education-live-session',
 'A plain-language introduction to IVF, ICSI, IUI, and the questions that help treatment conversations.',
 '<p>A live educational session designed to make common assisted-reproduction terms easier to understand.</p>',
 '2026-08-22 17:00:00', '2026-08-22 18:30:00', 'online', 'Instagram Live',
 'Community Event', 'https://instagram.com/emb_chronicles',
 'https://images.unsplash.com/photo-1542884748-2b87b36c6b90?auto=format&fit=crop&w=1200&q=85',
 'A presenter leading an educational online session', 0, 'published',
 'Fertility Education Live | Emb Chronicles',
 'A live fertility education session covering common treatment options.')
ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), description = VALUES(description);

INSERT INTO testimonials
(client_name, photo_path, photo_alt, quote, sort_order, is_visible)
VALUES
('Ike & Elizabeth Okafor', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=300&q=80', 'Ike and Elizabeth Okafor', 'Thank you very much for all the help you provided during the course of our treatment. It is not an easy journey, but your clarity and kindness helped us along each step of the way.', 1, 1),
('Karis & Boniface Lawal', 'https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=300&q=80', 'Karis and Boniface Lawal', 'Thank you for answering our questions with patience and kindness. We felt more able to understand the journey and have better conversations about our next steps.', 2, 1),
('Edward & Janis Tunde', 'https://images.unsplash.com/photo-1501901609772-df0848060b33?auto=format&fit=crop&w=300&q=80', 'Edward and Janis Tunde', 'The support helped us pinpoint the questions we needed to ask and gave us confidence to keep moving forward together.', 3, 1)
ON DUPLICATE KEY UPDATE client_name = VALUES(client_name);

INSERT INTO page_content
(page_key, section_key, eyebrow, heading, content, image_path, image_alt, link_label, link_url, status)
VALUES
('home', 'welcome', 'Welcome to your beginning', 'Welcome to EMB Chronicles',
 '<p>At Emb Chronicles, we do more than talk about fertility—we see the families that are coming into being. Our mission is to make the science of your fertility journey feel less like a mystery.</p><p>Through education, TTC community support, personal consultations, and live events, we walk alongside you from the first question to the final milestone. Think of us as your “Fertility Bestie”—the safe space between the clinic and the outside world.</p>',
 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=1200&q=85', 'A Black female scientist working carefully in a laboratory', 'More about us', '/about', 'published'),
('home', 'why', 'Why we exist', 'Science and hope belong in the same conversation',
 '<p>To be your safe space for everything needed on your fertility journey.</p>', '', '', 'Contact us', '/contact', 'published'),
('about', 'intro', 'Who we are', 'Science, empathy, and a clear way forward',
 '<p>Emb Chronicles makes fertility education accessible, compassionate, and science-driven. We turn clinical complexity into language people can use, questions they can ask, and decisions they can approach with confidence.</p>', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1200&q=85', 'A Black female health professional in a bright clinical environment', '', '', 'published'),
('about', 'guide', 'Meet your guide', 'Zubaida’s dream-chasing philosophy',
 '<p>With a B.Sc. in Microbiology and advanced post-graduate certification in Assisted Reproductive Technology from IMSA, Zubaida has spent nearly four years working in a leading fertility-clinic environment in Abuja.</p><p>Emb Chronicles grew from a simple conviction: people deserve the context, language, and confidence to participate more fully in decisions about their bodies, treatment, and careers.</p>', 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=85', 'A mentor speaking with people around a table', 'Book a conversation', '/appointment', 'published'),
('fiyff', 'mission', 'Fatima Ibrahim Yakubu Fertility Foundation', 'Making the path to parenthood more supported',
 '<p>FIYFF is the foundation arm of Emb Chronicles, dedicated to fertility awareness, advocacy, and practical financial support for eligible couples.</p>', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85', 'A diverse group sharing a supportive conversation', '', '', 'published'),
('community', 'vision', 'The vision', 'Your degree is not a dead end',
 '<p>“I know what it feels like to hold a Microbiology degree and wonder if it was all worth it. The path to becoming a Clinical Embryologist or STEM leader is not closed—it is waiting for the right roadmap.”</p>', '', '', '', '', 'published')
ON DUPLICATE KEY UPDATE eyebrow = VALUES(eyebrow), heading = VALUES(heading), content = VALUES(content);
