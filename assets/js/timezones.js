/**
 * Shared timezone options for profile / settings / onboarding selects.
 * Values use IANA timezone IDs; labels show city / country names.
 */
(function () {
  var TIMEZONE_GROUPS = [
    {
      label: 'UTC',
      options: [
        { value: 'UTC', text: 'UTC — Coordinated Universal Time' }
      ]
    },
    {
      label: 'United States — Eastern',
      options: [
        { value: 'America/New_York', text: 'New York, NY (Eastern Time)' },
        { value: 'America/Detroit', text: 'Detroit, MI (Eastern Time)' },
        { value: 'America/Indiana/Indianapolis', text: 'Indianapolis, IN (Eastern Time)' },
        { value: 'America/Kentucky/Louisville', text: 'Louisville, KY (Eastern Time)' },
        { value: 'America/New_York', text: 'Washington, D.C. (Eastern Time)', key: 'us-dc' },
        { value: 'America/New_York', text: 'Boston, MA (Eastern Time)', key: 'us-boston' },
        { value: 'America/New_York', text: 'Philadelphia, PA (Eastern Time)', key: 'us-philly' },
        { value: 'America/New_York', text: 'Atlanta, GA (Eastern Time)', key: 'us-atlanta' },
        { value: 'America/New_York', text: 'Miami, FL (Eastern Time)', key: 'us-miami' },
        { value: 'America/New_York', text: 'Orlando, FL (Eastern Time)', key: 'us-orlando' },
        { value: 'America/New_York', text: 'Tampa, FL (Eastern Time)', key: 'us-tampa' },
        { value: 'America/New_York', text: 'Jacksonville, FL (Eastern Time)', key: 'us-jacksonville' },
        { value: 'America/New_York', text: 'Charlotte, NC (Eastern Time)', key: 'us-charlotte' },
        { value: 'America/New_York', text: 'Raleigh, NC (Eastern Time)', key: 'us-raleigh' },
        { value: 'America/New_York', text: 'Richmond, VA (Eastern Time)', key: 'us-richmond' },
        { value: 'America/New_York', text: 'Baltimore, MD (Eastern Time)', key: 'us-baltimore' },
        { value: 'America/New_York', text: 'Columbus, OH (Eastern Time)', key: 'us-columbus' },
        { value: 'America/New_York', text: 'Cleveland, OH (Eastern Time)', key: 'us-cleveland' },
        { value: 'America/New_York', text: 'Pittsburgh, PA (Eastern Time)', key: 'us-pittsburgh' },
        { value: 'America/New_York', text: 'Buffalo, NY (Eastern Time)', key: 'us-buffalo' },
        { value: 'America/New_York', text: 'Rochester, NY (Eastern Time)', key: 'us-rochester' },
        { value: 'America/New_York', text: 'Hartford, CT (Eastern Time)', key: 'us-hartford' },
        { value: 'America/New_York', text: 'Providence, RI (Eastern Time)', key: 'us-providence' },
        { value: 'America/New_York', text: 'Newark, NJ (Eastern Time)', key: 'us-newark' }
      ]
    },
    {
      label: 'United States — Central',
      options: [
        { value: 'America/Chicago', text: 'Chicago, IL (Central Time)' },
        { value: 'America/Chicago', text: 'Dallas, TX (Central Time)', key: 'us-dallas' },
        { value: 'America/Chicago', text: 'Houston, TX (Central Time)', key: 'us-houston' },
        { value: 'America/Chicago', text: 'Austin, TX (Central Time)', key: 'us-austin' },
        { value: 'America/Chicago', text: 'San Antonio, TX (Central Time)', key: 'us-san-antonio' },
        { value: 'America/Chicago', text: 'Fort Worth, TX (Central Time)', key: 'us-fort-worth' },
        { value: 'America/Chicago', text: 'New Orleans, LA (Central Time)', key: 'us-new-orleans' },
        { value: 'America/Chicago', text: 'Baton Rouge, LA (Central Time)', key: 'us-baton-rouge' },
        { value: 'America/Chicago', text: 'Memphis, TN (Central Time)', key: 'us-memphis' },
        { value: 'America/Chicago', text: 'Nashville, TN (Central Time)', key: 'us-nashville' },
        { value: 'America/Chicago', text: 'Minneapolis, MN (Central Time)', key: 'us-minneapolis' },
        { value: 'America/Chicago', text: 'Kansas City, MO (Central Time)', key: 'us-kansas-city' },
        { value: 'America/Chicago', text: 'St. Louis, MO (Central Time)', key: 'us-st-louis' },
        { value: 'America/Chicago', text: 'Oklahoma City, OK (Central Time)', key: 'us-okc' },
        { value: 'America/Chicago', text: 'Tulsa, OK (Central Time)', key: 'us-tulsa' },
        { value: 'America/Chicago', text: 'Milwaukee, WI (Central Time)', key: 'us-milwaukee' },
        { value: 'America/Chicago', text: 'Omaha, NE (Central Time)', key: 'us-omaha' },
        { value: 'America/Chicago', text: 'Des Moines, IA (Central Time)', key: 'us-des-moines' },
        { value: 'America/Chicago', text: 'Little Rock, AR (Central Time)', key: 'us-little-rock' },
        { value: 'America/Chicago', text: 'Jackson, MS (Central Time)', key: 'us-jackson' },
        { value: 'America/North_Dakota/Center', text: 'Bismarck, ND (Central Time)' },
        { value: 'America/Menominee', text: 'Menominee, MI (Central Time)' }
      ]
    },
    {
      label: 'United States — Mountain',
      options: [
        { value: 'America/Denver', text: 'Denver, CO (Mountain Time)' },
        { value: 'America/Denver', text: 'Colorado Springs, CO (Mountain Time)', key: 'us-cos' },
        { value: 'America/Denver', text: 'Salt Lake City, UT (Mountain Time)', key: 'us-slc' },
        { value: 'America/Denver', text: 'Albuquerque, NM (Mountain Time)', key: 'us-abq' },
        { value: 'America/Boise', text: 'Boise, ID (Mountain Time)' },
        { value: 'America/Denver', text: 'Billings, MT (Mountain Time)', key: 'us-billings' },
        { value: 'America/Denver', text: 'Cheyenne, WY (Mountain Time)', key: 'us-cheyenne' },
        { value: 'America/Denver', text: 'Rapid City, SD (Mountain Time)', key: 'us-rapid-city' }
      ]
    },
    {
      label: 'United States — Arizona (no DST)',
      options: [
        { value: 'America/Phoenix', text: 'Phoenix, AZ' },
        { value: 'America/Phoenix', text: 'Tucson, AZ', key: 'us-tucson' },
        { value: 'America/Phoenix', text: 'Mesa, AZ', key: 'us-mesa' },
        { value: 'America/Phoenix', text: 'Scottsdale, AZ', key: 'us-scottsdale' },
        { value: 'America/Phoenix', text: 'Chandler, AZ', key: 'us-chandler' }
      ]
    },
    {
      label: 'United States — Pacific',
      options: [
        { value: 'America/Los_Angeles', text: 'Los Angeles, CA (Pacific Time)' },
        { value: 'America/Los_Angeles', text: 'San Francisco, CA (Pacific Time)', key: 'us-sf' },
        { value: 'America/Los_Angeles', text: 'San Diego, CA (Pacific Time)', key: 'us-sd' },
        { value: 'America/Los_Angeles', text: 'San Jose, CA (Pacific Time)', key: 'us-sj' },
        { value: 'America/Los_Angeles', text: 'Sacramento, CA (Pacific Time)', key: 'us-sac' },
        { value: 'America/Los_Angeles', text: 'Oakland, CA (Pacific Time)', key: 'us-oakland' },
        { value: 'America/Los_Angeles', text: 'Fresno, CA (Pacific Time)', key: 'us-fresno' },
        { value: 'America/Los_Angeles', text: 'Seattle, WA (Pacific Time)', key: 'us-seattle' },
        { value: 'America/Los_Angeles', text: 'Spokane, WA (Pacific Time)', key: 'us-spokane' },
        { value: 'America/Los_Angeles', text: 'Portland, OR (Pacific Time)', key: 'us-portland' },
        { value: 'America/Los_Angeles', text: 'Las Vegas, NV (Pacific Time)', key: 'us-vegas' },
        { value: 'America/Los_Angeles', text: 'Reno, NV (Pacific Time)', key: 'us-reno' }
      ]
    },
    {
      label: 'United States — Alaska & Hawaii',
      options: [
        { value: 'America/Anchorage', text: 'Anchorage, AK (Alaska Time)' },
        { value: 'America/Juneau', text: 'Juneau, AK (Alaska Time)' },
        { value: 'America/Sitka', text: 'Sitka, AK (Alaska Time)' },
        { value: 'America/Nome', text: 'Nome, AK (Alaska Time)' },
        { value: 'America/Adak', text: 'Adak, AK (Hawaii-Aleutian Time)' },
        { value: 'Pacific/Honolulu', text: 'Honolulu, HI (Hawaii Time)' }
      ]
    },
    {
      label: 'Canada',
      options: [
        { value: 'America/Toronto', text: 'Toronto, Canada (Eastern Time)' },
        { value: 'America/Toronto', text: 'Ottawa, Canada (Eastern Time)', key: 'ca-ottawa' },
        { value: 'America/Toronto', text: 'Montreal, Canada (Eastern Time)', key: 'ca-montreal' },
        { value: 'America/Halifax', text: 'Halifax, Canada (Atlantic Time)' },
        { value: 'America/St_Johns', text: "St. John's, Canada (Newfoundland Time)" },
        { value: 'America/Winnipeg', text: 'Winnipeg, Canada (Central Time)' },
        { value: 'America/Regina', text: 'Regina, Canada (Central Time)' },
        { value: 'America/Edmonton', text: 'Edmonton, Canada (Mountain Time)' },
        { value: 'America/Edmonton', text: 'Calgary, Canada (Mountain Time)', key: 'ca-calgary' },
        { value: 'America/Vancouver', text: 'Vancouver, Canada (Pacific Time)' }
      ]
    },
    {
      label: 'Mexico & Latin America',
      options: [
        { value: 'America/Mexico_City', text: 'Mexico City, Mexico' },
        { value: 'America/Cancun', text: 'Cancún, Mexico' },
        { value: 'America/Tijuana', text: 'Tijuana, Mexico' },
        { value: 'America/Bogota', text: 'Bogotá, Colombia' },
        { value: 'America/Lima', text: 'Lima, Peru' },
        { value: 'America/Santiago', text: 'Santiago, Chile' },
        { value: 'America/Argentina/Buenos_Aires', text: 'Buenos Aires, Argentina' },
        { value: 'America/Sao_Paulo', text: 'São Paulo, Brazil' },
        { value: 'America/Caracas', text: 'Caracas, Venezuela' },
        { value: 'America/Panama', text: 'Panama City, Panama' },
        { value: 'America/Jamaica', text: 'Kingston, Jamaica' },
        { value: 'America/Puerto_Rico', text: 'San Juan, Puerto Rico' }
      ]
    },
    {
      label: 'Europe',
      options: [
        { value: 'Europe/London', text: 'London, United Kingdom' },
        { value: 'Europe/Dublin', text: 'Dublin, Ireland' },
        { value: 'Europe/Paris', text: 'Paris, France' },
        { value: 'Europe/Berlin', text: 'Berlin, Germany' },
        { value: 'Europe/Madrid', text: 'Madrid, Spain' },
        { value: 'Europe/Rome', text: 'Rome, Italy' },
        { value: 'Europe/Amsterdam', text: 'Amsterdam, Netherlands' },
        { value: 'Europe/Brussels', text: 'Brussels, Belgium' },
        { value: 'Europe/Zurich', text: 'Zurich, Switzerland' },
        { value: 'Europe/Vienna', text: 'Vienna, Austria' },
        { value: 'Europe/Stockholm', text: 'Stockholm, Sweden' },
        { value: 'Europe/Oslo', text: 'Oslo, Norway' },
        { value: 'Europe/Copenhagen', text: 'Copenhagen, Denmark' },
        { value: 'Europe/Helsinki', text: 'Helsinki, Finland' },
        { value: 'Europe/Warsaw', text: 'Warsaw, Poland' },
        { value: 'Europe/Prague', text: 'Prague, Czech Republic' },
        { value: 'Europe/Athens', text: 'Athens, Greece' },
        { value: 'Europe/Istanbul', text: 'Istanbul, Turkey' },
        { value: 'Europe/Moscow', text: 'Moscow, Russia' },
        { value: 'Europe/Lisbon', text: 'Lisbon, Portugal' }
      ]
    },
    {
      label: 'Africa',
      options: [
        { value: 'Africa/Cairo', text: 'Cairo, Egypt' },
        { value: 'Africa/Lagos', text: 'Lagos, Nigeria' },
        { value: 'Africa/Johannesburg', text: 'Johannesburg, South Africa' },
        { value: 'Africa/Nairobi', text: 'Nairobi, Kenya' },
        { value: 'Africa/Casablanca', text: 'Casablanca, Morocco' },
        { value: 'Africa/Accra', text: 'Accra, Ghana' }
      ]
    },
    {
      label: 'Asia & Middle East',
      options: [
        { value: 'Asia/Dubai', text: 'Dubai, United Arab Emirates' },
        { value: 'Asia/Riyadh', text: 'Riyadh, Saudi Arabia' },
        { value: 'Asia/Jerusalem', text: 'Jerusalem, Israel' },
        { value: 'Asia/Karachi', text: 'Karachi, Pakistan' },
        { value: 'Asia/Karachi', text: 'Islamabad, Pakistan', key: 'pk-islamabad' },
        { value: 'Asia/Kolkata', text: 'Mumbai, India' },
        { value: 'Asia/Kolkata', text: 'New Delhi, India', key: 'in-delhi' },
        { value: 'Asia/Kolkata', text: 'Kolkata, India', key: 'in-kolkata' },
        { value: 'Asia/Dhaka', text: 'Dhaka, Bangladesh' },
        { value: 'Asia/Bangkok', text: 'Bangkok, Thailand' },
        { value: 'Asia/Singapore', text: 'Singapore' },
        { value: 'Asia/Hong_Kong', text: 'Hong Kong' },
        { value: 'Asia/Shanghai', text: 'Shanghai, China' },
        { value: 'Asia/Shanghai', text: 'Beijing, China', key: 'cn-beijing' },
        { value: 'Asia/Tokyo', text: 'Tokyo, Japan' },
        { value: 'Asia/Seoul', text: 'Seoul, South Korea' },
        { value: 'Asia/Manila', text: 'Manila, Philippines' },
        { value: 'Asia/Jakarta', text: 'Jakarta, Indonesia' }
      ]
    },
    {
      label: 'Australia & Pacific',
      options: [
        { value: 'Australia/Sydney', text: 'Sydney, Australia' },
        { value: 'Australia/Melbourne', text: 'Melbourne, Australia' },
        { value: 'Australia/Brisbane', text: 'Brisbane, Australia' },
        { value: 'Australia/Perth', text: 'Perth, Australia' },
        { value: 'Australia/Adelaide', text: 'Adelaide, Australia' },
        { value: 'Pacific/Auckland', text: 'Auckland, New Zealand' },
        { value: 'Pacific/Fiji', text: 'Suva, Fiji' },
        { value: 'Pacific/Guam', text: 'Guam' }
      ]
    }
  ];

  var LEGACY_MAP = {
    'UTC': 'UTC',
    'Eastern Time': 'America/New_York',
    'Central Time': 'America/Chicago',
    'Mountain Time': 'America/Denver',
    'Pacific Time': 'America/Los_Angeles',
    'Arizona': 'America/Phoenix',
    'Alaska': 'America/Anchorage'
  };

  function buildOptions(select) {
    var placeholder = select.querySelector('option[value=""]');
    var previous = select.value;
    var preferred =
      select.getAttribute('data-selected') ||
      LEGACY_MAP[previous] ||
      previous ||
      '';

    select.innerHTML = '';

    if (placeholder || select.hasAttribute('data-placeholder')) {
      var empty = document.createElement('option');
      empty.value = '';
      empty.textContent = (placeholder && placeholder.textContent) || select.getAttribute('data-placeholder') || 'Select timezone';
      select.appendChild(empty);
    }

    TIMEZONE_GROUPS.forEach(function (group) {
      var optgroup = document.createElement('optgroup');
      optgroup.label = group.label;

      group.options.forEach(function (item) {
        var option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.text;
        if (item.key) option.setAttribute('data-city', item.key);
        optgroup.appendChild(option);
      });

      select.appendChild(optgroup);
    });

    if (!preferred) return;

    var mapped = LEGACY_MAP[preferred] || preferred;
    var matched = Array.prototype.find.call(select.options, function (opt) {
      return opt.value === mapped || opt.value === preferred;
    });

    if (matched) matched.selected = true;
  }

  function initTimezoneSelects() {
    var selects = document.querySelectorAll(
      'select[name="timezone"], select#profile-timezone, select#settings-timezone, select#ss-timezone, select#timezone'
    );
    selects.forEach(buildOptions);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTimezoneSelects);
  } else {
    initTimezoneSelects();
  }
})();
