# Chapter System Implementation - Complete Guide

## Overview
The Chapter system allows alumni to join location-based chapters. The system supports:
- **Country-wide chapters** (e.g., Ghana, Nigeria)
- **City/State-based chapters** (e.g., New York, London)
- **Configurable by admins** per country
- **Future-ready** for multiple chapter memberships

## Database Tables Created

### 1. `chapters`
Stores all chapter information
- `chapter_uuid` - Unique identifier
- `name` - Chapter name (e.g., "Ghana Chapter", "New York Chapter")
- `code` - Unique code (e.g., "GH", "US-NY")
- `type` - 'country' or 'city'
- `country_code` - ISO country code
- `country_name` - Full country name
- `state_province` - State/Province (for city chapters)
- `city` - City name (for city chapters)
- `contact_email` - Chapter contact email
- `contact_phone` - Chapter contact phone
- `is_active` - Active status

### 2. `country_chapter_configurations`
Defines how chapters work for each country
- `country_code` - ISO country code
- `country_name` - Full country name
- `chapter_type` - 'country' or 'city'
- `allow_multiple_chapters` - For future use
- `is_active` - Active status
- `notes` - Additional notes

### 3. `chapter_user` (Pivot Table)
Links users to chapters
- `user_id` - User ID
- `chapter_id` - Chapter ID
- `is_primary` - Primary chapter flag
- `membership_status` - 'active', 'inactive', or 'pending'
- `joined_at` - When user joined

### 4. `users` (Updated)
Added location fields:
- `state_province_of_residence`
- `city_of_residence`

## Sample Data Populated

### Country Configurations
- **Ghana (GH)** - Country-wide chapter
- **United States (US)** - City-based chapters
- **United Kingdom (GB)** - City-based chapters
- **Nigeria (NG)** - Country-wide chapter
- **South Africa (ZA)** - Country-wide chapter
- **Canada (CA)** - City-based chapters

### Sample Chapters Created
1. **Ghana Chapter** (GH)
2. **New York Chapter** (US-NY)
3. **Washington DC Chapter** (US-DC)
4. **California Chapter** (US-CA)
5. **Texas Chapter** (US-TX)
6. **London Chapter** (GB-LDN)
7. **Manchester Chapter** (GB-MAN)
8. **Nigeria Chapter** (NG)
9. **South Africa Chapter** (ZA)
10. **Toronto Chapter** (CA-TOR)
11. **Vancouver Chapter** (CA-VAN)

## API Endpoints

### Alumni Endpoints (Protected - auth:api)

#### Get All Chapters (Browse)
```
GET /api/alumni/chapters
Query params: ?per_page=15&country_code=US&type=city
```

#### Get Available Chapters for User
```
GET /api/alumni/chapters/available
```
Returns chapters based on user's country of residence. Includes suggested chapter.

#### Get User's Current Chapter
```
GET /api/alumni/chapters/my-chapter
```

#### Get Chapter Details
```
GET /api/alumni/chapters/{id}
```

#### Join a Chapter
```
POST /api/alumni/chapters/join
Body: { "chapter_id": 1 }
```

#### Leave Current Chapter
```
POST /api/alumni/chapters/leave
```

### Admin Endpoints (Protected - auth:admin)

#### List All Chapters
```
GET /api/admin/chapters
Query params: ?type=city&country_code=US&search=New&is_active=1&per_page=15
```

#### Get Chapter Statistics
```
GET /api/admin/chapters/statistics
```
Returns:
- Total chapters
- Active chapters
- Country vs City chapters
- Total members
- Chapters by country

#### Create Chapter
```
POST /api/admin/chapters
Body: {
  "name": "Atlanta Chapter",
  "code": "US-ATL",
  "description": "...",
  "type": "city",
  "country_code": "US",
  "country_name": "United States",
  "state_province": "Georgia",
  "city": "Atlanta",
  "contact_email": "atlanta@ugalumni.org",
  "contact_phone": "+1 404 555 0100",
  "is_active": true
}
```

#### Update Chapter
```
PUT /api/admin/chapters/{id}
Body: { "name": "...", "contact_email": "..." }
```

#### Delete Chapter
```
DELETE /api/admin/chapters/{id}
```
Note: Cannot delete chapters with active members

#### Get Chapter Members
```
GET /api/admin/chapters/{id}/members
Query params: ?per_page=15&search=john
```

#### Get Chapter Details
```
GET /api/admin/chapters/{id}
```

### Country Configuration Endpoints (Admin)

#### List All Country Configurations
```
GET /api/admin/country-configurations
```

#### Get Configuration for Specific Country
```
GET /api/admin/country-configurations/{countryCode}
```

#### Create/Update Country Configuration
```
POST /api/admin/country-configurations
Body: {
  "country_code": "KE",
  "country_name": "Kenya",
  "chapter_type": "country",
  "allow_multiple_chapters": false,
  "is_active": true,
  "notes": "Kenya uses country-wide chapter"
}
```

#### Delete Country Configuration
```
DELETE /api/admin/country-configurations/{countryCode}
```

## How It Works

### For Admins:

1. **Configure a country** via `/api/admin/country-configurations`
   - Set `chapter_type` to 'country' or 'city'
   
2. **Create chapters** via `/api/admin/chapters`
   - For country-wide: Only create one chapter per country
   - For city-based: Create multiple chapters for different cities

3. **Manage chapters**
   - View all chapters and their members
   - Update chapter information
   - Activate/deactivate chapters
   - View statistics

### For Alumni Users:

1. **Update profile** with location information
   - Set `country_of_residence`
   - Set `city_of_residence` (for city-based countries)

2. **Browse available chapters**
   - System shows chapters based on user's location
   - System suggests appropriate chapter

3. **Join a chapter**
   - Currently limited to one chapter
   - System prevents joining multiple chapters

4. **View chapter details**
   - See chapter information
   - See number of members

## Key Features

### Smart Chapter Suggestion
The system automatically suggests the appropriate chapter based on:
- User's country of residence
- Country's chapter configuration (country-wide vs city-based)
- User's city of residence (for city-based countries)

### Flexible Configuration
- Admins can configure each country independently
- Easy to switch between country-wide and city-based
- Ready for future multiple chapter memberships

### Data Integrity
- Cannot delete chapters with active members
- Cannot delete country configurations with existing chapters
- Unique constraints on chapter codes
- Foreign key constraints for data integrity

### Future-Ready
- `allow_multiple_chapters` field for future expansion
- `is_primary` flag in pivot table
- `membership_status` for different membership states

## Models and Relationships

### Chapter Model
```php
// Relationships
$chapter->users()          // All users in chapter
$chapter->activeMembers()  // Active members only
$chapter->countryConfiguration()  // Country config

// Scopes
Chapter::active()          // Active chapters
Chapter::countryBased()    // Country-wide chapters
Chapter::cityBased()       // City-based chapters

// Attributes
$chapter->members_count    // Count of active members
```

### User Model
```php
// Relationships
$user->chapter()           // Get primary chapter
$user->chapters()          // All chapters (future use)

// Methods
$user->assignToChapter($id, $isPrimary = true)
$user->getSuggestedChapter()

// Usage
$suggestedChapter = $user->getSuggestedChapter();
if ($suggestedChapter) {
    $user->assignToChapter($suggestedChapter->id);
}
```

### CountryChapterConfiguration Model
```php
// Relationships
$config->chapters()        // All chapters in country

// Methods
$config->usesCityChapters()      // Returns true if city-based
$config->usesCountryChapter()    // Returns true if country-wide
```

## Testing the System

### 1. Create Country Configuration
```bash
POST /api/admin/country-configurations
{
  "country_code": "KE",
  "country_name": "Kenya",
  "chapter_type": "country",
  "is_active": true
}
```

### 2. Create Chapter
```bash
POST /api/admin/chapters
{
  "name": "Kenya Chapter",
  "code": "KE",
  "type": "country",
  "country_code": "KE",
  "country_name": "Kenya",
  "is_active": true
}
```

### 3. User Updates Profile
```bash
PUT /api/alumni/profile
{
  "country_of_residence": "KE"
}
```

### 4. User Gets Available Chapters
```bash
GET /api/alumni/chapters/available
```

### 5. User Joins Chapter
```bash
POST /api/alumni/chapters/join
{
  "chapter_id": 1
}
```

## Migration Commands Run

```bash
php artisan migrate
php artisan db:seed --class=CountryChapterConfigurationSeeder
php artisan db:seed --class=ChapterSeeder
```

## Files Created

### Migrations
- `2025_11_04_200001_create_chapters_table.php`
- `2025_11_04_200002_create_country_chapter_configurations_table.php`
- `2025_11_04_200003_create_chapter_user_table.php`
- `2025_11_04_200004_add_location_fields_to_users_table.php`

### Models
- `app/Models/Chapter.php`
- `app/Models/CountryChapterConfiguration.php`
- `app/Models/User.php` (updated)

### Controllers
- `app/Http/Controllers/Admin/ChapterManagementController.php`
- `app/Http/Controllers/Admin/CountryChapterConfigurationController.php`
- `app/Http/Controllers/UserChapterController.php`

### Seeders
- `database/seeders/CountryChapterConfigurationSeeder.php`
- `database/seeders/ChapterSeeder.php`

### Routes
- All routes added to `routes/api.php`

## Next Steps

1. **Update Registration Form** to collect city/state information
2. **Create Chapter Dashboard** for admins
3. **Add Chapter Events** system (future feature)
4. **Enable Multiple Chapters** when needed (just update the join logic)
5. **Add Chapter Communication** features

## Support for Multiple Chapters (Future)

The system is ready for multiple chapters. To enable:

1. Update the `joinChapter` method to allow multiple memberships
2. Remove the check that prevents joining if user already has a chapter
3. The pivot table already supports multiple memberships with `is_primary` flag

```php
// Future implementation
public function joinChapter(Request $request) {
    // Remove this check:
    // if ($user->chapters()->wherePivot('is_primary', true)->exists())
    
    // Allow user to join multiple chapters
    $user->assignToChapter($chapter->id, $isPrimary = false);
}
```

---

## Summary

✅ Database tables created and migrated
✅ Models with relationships implemented
✅ Admin chapter management endpoints
✅ Alumni chapter browsing and joining endpoints
✅ Smart chapter suggestion system
✅ Sample data populated (6 countries, 11 chapters)
✅ Flexible configuration system
✅ Future-proof for multiple memberships

The Chapter system is fully functional and ready to use!

