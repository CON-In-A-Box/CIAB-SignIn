INSERT INTO ConfigurationField (Field, TargetTable, Type, InitialValue, Description) VALUES
    ('ADMINEMAIL', 'Configuration', 'text', '', 'Email Address for Site ADMIN contact.'),
    ('CONCOMHOURS', 'Configuration', 'integer', 60, 'Volunteer hours given to Convention Staff.'),
    ('ADMINACCOUNTS', 'Configuration', 'list', '', 'List of account IDs who are site administrators.'),
    ('CONHOST', 'Configuration', 'text', '', 'Name of the orginization putting on the convention.'),
    ('DISABLEDMODULES', 'Configuration', 'list', '', 'List of modules disabled on the site.'),
    ('FEEDBACK_EMAIL', 'Configuration', 'text', '', 'Email Address for feedback on the site.'),
    ('HELP_EMAIL', 'Configuration', 'text', '', 'Email Address for help on the site.'),
    ('MAXLOGINFAIL', 'Configuration', 'integer', 5, 'Number of time login can fail before an account is locked.'),
    ('NOREPLY_EMAIL', 'Configuration', 'text', '', 'Email Address for no-reply messages.'),
    ('PASSWORDEXPIRE', 'Configuration', 'text', '+1 year', 'Time period after which an account authentication expires.'),
    ('PASSWORDRESET', 'Configuration', 'text', '+60 minutes', 'Time period after which a reset request expires.'),
    ('SECURITY_EMAIL', 'Configuration', 'text', '', 'Email Address for security messages.'),
    ('TIMEZONE', 'Configuration', 'text', 'America/Chicago', 'Timezone for the event.'),
    ('NEONID', 'Configuration', 'text', '', 'ID for Neon integration'),
    ('NEONKEY', 'Configuration', 'text', '', 'Key for Neon integration'),
    ('NEONTRIAL', 'Configuration', 'boolean', 0, 'Neon integration is with BETA neon ID.'),
    ('col.primary', 'Configuration', 'text', '#FFF', 'HTML Color key for primary site color.'),
    ('col.prim-back', 'Configuration', 'text', '#4CAF50', 'HTML Color key for primary site background color.'),
    ('col.secondary', 'Configuration', 'text', '#FFF', 'HTML Color key for secondary site color.'),
    ('col.second-back', 'Configuration', 'text', '#2196F3', 'HTML Color key for secondary site background color.'),
    ('ASSET_BACKEND', 'Configuration', 'text', 'file.inc', 'Asset Library Backend.'),
    ('FILE_ASSET_DATA', 'Configuration', 'boolean', 0, 'Asset path is not web accessable.'),
    ('FILE_ASSET_PATH', 'Configuration', 'text', 'resources/images', 'Asset data path.')
