# MAK Digital Arena ? implemented structure

Project: `D:\makdigital`. Laravel/Blade/Vite structure follows the inspected indorelitfest project without copying its application or credentials.

| Location | Responsibility |
| --- | --- |
| app/Http/Controllers/Frontend | Public pages and enquiry submissions |
| app/Http/Controllers/Admin | Authentication, content, enquiries, settings |
| app/Http/Requests | Enquiry and media/content validation |
| app/Models | ContentEntry, SiteSetting, Enquiry, LegacyPage, User |
| app/Services/SiteContent.php | Published content and contact settings |
| app/Jobs/NotifyEnquiry.php | Queued notification and failure state |
| app/Console/Commands | Legacy import and secure administrator creation |
| database/migrations, database/seeders | Schema and curated initial content |
| resources/views/frontend | Public pages and shared sections |
| resources/views/admin | Administrative views |
| resources/views/layouts/site.blade.php | Shared navigation, metadata and footer |
| resources/css/frontend, resources/js/frontend | Design, accessible interactions and lazy 3D |
| public/images | Curated responsive media and uploaded WebP images |
| public/build | Vite production bundles and local fonts |
| config/legacy-redirects.php | Explicit legacy URL mappings |
| scripts | Repeatable archive, asset preparation and browser review |
| storage/app/legacy/mdarena | Private original HTML/text, 484 assets, discovery and failure reports |
| storage/app/review | Reference screenshots and verification artifacts |
| storage/app/private | Local administrator access file; never public |
| tests/Feature | Import, public pages, enquiries, authentication and admin coverage |

Only `public/` is the web root. Historical scripts remain private source artifacts and are not executed by the new site. SQLite supports immediate local use; MySQL credentials can be configured for deployment.
