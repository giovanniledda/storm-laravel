<?php


/*
 * BOAT, SECTIONS & SIMILAR
 */

defined('SECTION_NAME_LEFT_SIDE') or define('SECTION_NAME_LEFT_SIDE', 'left_side');
defined('SECTION_NAME_RIGHT_SIDE') or define('SECTION_NAME_RIGHT_SIDE',  'right_side');
defined('SECTION_NAME_DECK') or define('SECTION_NAME_DECK', 'deck');


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
 * PROJECT TYPES
 */

defined('PROJECT_TYPE_NEWBUILD') or define('PROJECT_TYPE_NEWBUILD', 'newbuild');
defined('PROJECT_TYPE_REFIT') or define('PROJECT_TYPE_REFIT', 'refit');

/*
 * TASKS  STATUS
 */

defined('TASKS_STATUS_DRAFT') or define('TASKS_STATUS_DRAFT', 'draft');
defined('TASKS_STATUS_SUBMITTED') or define('TASKS_STATUS_SUBMITTED', 'submitted');
defined('TASKS_STATUS_ACCEPTED') or define('TASKS_STATUS_ACCEPTED', 'accepted');
defined('TASKS_STATUS_CLOSED') or define('TASKS_STATUS_CLOSED', 'closed');
defined('TASKS_STATUS_DENIED') or define('TASKS_STATUS_DENIED', 'denied');
defined('TASKS_STATUSES') or define('TASKS_STATUSES', [TASKS_STATUS_DRAFT, TASKS_STATUS_SUBMITTED, TASKS_STATUS_ACCEPTED, TASKS_STATUS_CLOSED, TASKS_STATUS_DENIED]);
