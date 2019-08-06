<?php


/*
 * BOAT, SECTIONS & SIMILAR
 */

defined('SECTION_TYPE_LEFT_SIDE') or define('SECTION_TYPE_LEFT_SIDE', 'left_side');
defined('SECTION_TYPE_RIGHT_SIDE') or define('SECTION_TYPE_RIGHT_SIDE',  'right_side');
defined('SECTION_TYPE_DECK') or define('SECTION_TYPE_DECK', 'deck');


/*
 * USERS
 */

defined('STORM_EMAIL_SUFFIX') or define('STORM_EMAIL_SUFFIX', '@storm.net');


/*
 * ROLES
 */

defined('ROLE_ADMIN') or define('ROLE_ADMIN', 'admin');
defined('ROLE_ADMIN_LABEL') or define('ROLE_ADMIN_LABEL', 'Admin');
defined('ROLE_BOAT_MANAGER') or define('ROLE_BOAT_MANAGER', 'boat_manager');
defined('ROLE_BOAT_MANAGER_LABEL') or define('ROLE_BOAT_MANAGER_LABEL', 'Boat manager');
defined('ROLE_BACKEND_MANAGER') or define('ROLE_BACKEND_MANAGER', 'backend_manager');
defined('ROLE_BACKEND_MANAGER_LABEL') or define('ROLE_BACKEND_MANAGER_LABEL', 'Backend manager');
defined('ROLE_WORKER') or define('ROLE_WORKER', 'worker');
defined('ROLE_WORKER_LABEL') or define('ROLE_WORKER_LABEL', 'Worker');


/*
 * PERMISSIONS
 */

defined('PERMISSION_ADMIN') or define('PERMISSION_ADMIN', 'admin');
defined('PERMISSION_BOAT_MANAGER') or define('PERMISSION_BOAT_MANAGER', 'boat_manager');
defined('PERMISSION_BACKEND_MANAGER') or define('PERMISSION_BACKEND_MANAGER', 'backend_manager');
defined('PERMISSION_WORKER') or define('PERMISSION_WORKER', 'worker');

/*
 * UPDATES/NOTIFICATIONS
 */

defined('TASK_CREATED_MOBILE_APP_TEXT') or define('TASK_CREATED_MOBILE_APP_TEXT', '@someone just created Task @task_id, on Project @project_name, Boat @boat_name.');
defined('TASK_UPDATED_MOBILE_APP_TEXT') or define('TASK_UPDATED_MOBILE_APP_TEXT', 'Task @task_id, on Project @project_name, Boat @boat_name has been updated by @someone.');


/*
 * PROJECT STATUSES
 */

defined('PROJECT_STATUS_OPEN') or define('PROJECT_STATUS_OPEN', 'open');
defined('PROJECT_STATUS_CLOSED') or define('PROJECT_STATUS_CLOSED', 'closed');
defined('PROJECT_STATUSES') or define('PROJECT_STATUSES', [PROJECT_STATUS_OPEN, PROJECT_STATUS_CLOSED]);


/*
 * PROJECT-USERS ROLES
 */

defined('PROJECT_USER_ROLE_AUTHOR') or define('PROJECT_USER_ROLE_AUTHOR', 'author');
defined('PROJECT_USER_ROLE_OWNER') or define('PROJECT_USER_ROLE_OWNER', 'owner'); //?? discutere con danilo

/*
 * PROJECT EVENT TYPES
 */

defined('PROJECT_EVENT_TYPE_MARK_COMPLETED') or define('PROJECT_EVENT_TYPE_MARK_COMPLETED', 1);
defined('PROJECT_EVENT_TYPE_PROGRESS') or define('PROJECT_EVENT_TYPE_PROGRESS', 2);

/*
 * PROJECT EVENTS STRINGS 
 */

defined('PROJECT_EVENT_MARK_COMPLETED') or define('PROJECT_EVENT_MARK_COMPLETED', ' points marked as completed');
defined('PROJECT_EVENT_PROGRESS') or define('PROJECT_EVENT_PROGRESS', '% percentage');



/*
 * PROJECT TYPES
 */

defined('PROJECT_TYPE_NEWBUILD') or define('PROJECT_TYPE_NEWBUILD', 'newbuild');
defined('PROJECT_TYPE_REFIT') or define('PROJECT_TYPE_REFIT', 'refit');

/*
 * BOAT TYPES
 */

defined('BOAT_TYPE_SAIL') or define('BOAT_TYPE_SAIL', 'S/Y');
defined('BOAT_TYPE_MOTOR') or define('BOAT_TYPE_MOTOR', 'M/Y');

/*
 * TASKS  STATUS
 */

defined('TASKS_STATUS_DRAFT') or define('TASKS_STATUS_DRAFT', 'draft');
defined('TASKS_STATUS_SUBMITTED') or define('TASKS_STATUS_SUBMITTED', 'submitted');
defined('TASKS_STATUS_ACCEPTED') or define('TASKS_STATUS_ACCEPTED', 'accepted');
defined('TASKS_STATUS_CLOSED') or define('TASKS_STATUS_CLOSED', 'closed');
defined('TASKS_STATUS_DENIED') or define('TASKS_STATUS_DENIED', 'denied');
defined('TASKS_STATUSES') or define('TASKS_STATUSES', [TASKS_STATUS_DRAFT, TASKS_STATUS_SUBMITTED, TASKS_STATUS_ACCEPTED, TASKS_STATUS_CLOSED, TASKS_STATUS_DENIED]);


/*
 * VALIDATORS MESSAGES
 */

defined('VALIDATOR_REQUIRED') or define('VALIDATOR_REQUIRED', 'is required and cannot be null');
defined('VALIDATOR_STRING') or define('VALIDATOR_STRING', 'is not a string');
defined('VALIDATOR_NUMERIC') or define('VALIDATOR_NUMERIC', 'is not a numeric value');
defined('VALIDATOR_IN') or define('VALIDATOR_IN', 'is not a valid value, valid values are : ');


/*
 * RESET PASSWORD MESSAGES
 */

defined('PASSWORD_RESET_LINK_SENT') or define('PASSWORD_RESET_LINK_SENT', 'Password reset link sent via email!');
