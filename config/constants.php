<?php


/*
 * BOAT, SECTIONS & SIMILAR
 */

defined('SECTION_TYPE_LEFT_SIDE') or define('SECTION_TYPE_LEFT_SIDE', 'left_side');
defined('SECTION_TYPE_RIGHT_SIDE') or define('SECTION_TYPE_RIGHT_SIDE',  'right_side');
defined('SECTION_TYPE_DECK') or define('SECTION_TYPE_DECK', 'deck');
defined('SECTION_IMAGE_POINTS_OVERVIEW') or define('SECTION_IMAGE_POINTS_OVERVIEW', 'points_overview_image');


/*
 * USERS
 */

defined('STORM_EMAIL_SUFFIX') or define('STORM_EMAIL_SUFFIX', '@storm.net');
defined('USER_FAKE_PASSWORD') or define('USER_FAKE_PASSWORD', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
defined('USER_PHONE_TYPE_MOBILE') or define('USER_PHONE_TYPE_MOBILE', 'mobile');
defined('USER_PHONE_TYPE_FIXED') or define('USER_PHONE_TYPE_FIXED', 'fixed line');
defined('USER_PHONE_TYPES') or define('USER_PHONE_TYPES', [USER_PHONE_TYPE_MOBILE, USER_PHONE_TYPE_FIXED]);


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
 *
 * OPERATIONAL / ISPECTION
 *
 * refit e new build sono gestiti da project_type.
 *
 */

defined('PROJECT_STATUS_OPERATIONAL') or define('PROJECT_STATUS_OPERATIONAL', 'operational'); // a mare
defined('PROJECT_STATUS_IN_SITE') or define('PROJECT_STATUS_IN_SITE', 'in_site'); // in refit in cantiere.
defined('PROJECT_STATUS_CLOSED') or define('PROJECT_STATUS_CLOSED', 'closed');

defined('PROJECT_STATUSES') or define('PROJECT_STATUSES', [PROJECT_STATUS_IN_SITE, PROJECT_STATUS_OPERATIONAL, PROJECT_STATUS_CLOSED]);


/*
 * PROJECT-USERS ROLES
 */

defined('PROJECT_USER_ROLE_AUTHOR') or define('PROJECT_USER_ROLE_AUTHOR', 'author');
defined('PROJECT_USER_ROLE_OWNER') or define('PROJECT_USER_ROLE_OWNER', 'owner'); //?? discutere con danilo


/*
 * PROJECT EVENTS STRINGS
 */

defined('PROJECT_EVENT_MARK_COMPLETED') or define('PROJECT_EVENT_MARK_COMPLETED', ' marked as completed');
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
accepted (l'utente l'ha inviato e il backend l'ha accettato)
in_progress (questo stato non e' presente nell'inVision capire se serve - il task e' in lavorazione)
denied (l'utente l'ha inviato e il backend l'ha rifiutato)
remarked (il backend l'ha chiuso, ma l'utente l'ha segnalato come non concluso. E' lo stesso stato di "in lavorazione" se lo mettiamo, oppure di accepted altrimenti)
monitored (e' ancora aperto, ma il progetto e' chiuso, e sono i task da tenere d'occhio)
 *
 *
 */

defined('TASKS_STATUS_DRAFT') or define('TASKS_STATUS_DRAFT', 'draft');
defined('TASKS_STATUS_SUBMITTED') or define('TASKS_STATUS_SUBMITTED', 'submitted');
defined('TASKS_STATUS_ACCEPTED') or define('TASKS_STATUS_ACCEPTED', 'accepted');
defined('TASKS_STATUS_IN_PROGRESS') or define('TASKS_STATUS_IN_PROGRESS', 'in progress');
defined('TASKS_STATUS_DENIED') or define('TASKS_STATUS_DENIED', 'denied');
defined('TASKS_STATUS_MONITORED') or define('TASKS_STATUS_MONITORED', 'monitored');
defined('TASKS_STATUS_COMPLETED') or define('TASKS_STATUS_COMPLETED', 'completed');
defined('TASKS_STATUSES') or define('TASKS_STATUSES',
        [
         TASKS_STATUS_DRAFT,
         TASKS_STATUS_SUBMITTED,
         TASKS_STATUS_DENIED,
         TASKS_STATUS_ACCEPTED,
         TASKS_STATUS_IN_PROGRESS,
         TASKS_STATUS_COMPLETED,
         TASKS_STATUS_MONITORED,
         ]);

defined('TASKS_STATUS_R_OPEN') or define('TASKS_STATUS_R_OPEN', 'open');  // _R: Remark
defined('TASKS_STATUS_R_CLOSED') or define('TASKS_STATUS_R_CLOSED', 'closed');  // _R: Remark
defined('TASKS_STATUS_R_NO_ACTIONS') or define('TASKS_STATUS_R_NO_ACTIONS', 'no_actions');  // _R: Remark
defined('TASKS_STATUS_R_LOCAL_REPAINT') or define('TASKS_STATUS_R_LOCAL_REPAINT', 'local_repaint');  // _R: Remark
defined('TASKS_STATUS_R_TOTAL_REPAINT') or define('TASKS_STATUS_R_TOTAL_REPAINT', 'total_repaint');  // _R: Remark
defined('TASKS_R_STATUSES') or define('TASKS_R_STATUSES',
        [
        TASKS_STATUS_R_OPEN,
        TASKS_STATUS_R_CLOSED,
        TASKS_STATUS_R_NO_ACTIONS,
        TASKS_STATUS_R_LOCAL_REPAINT,
        TASKS_STATUS_R_TOTAL_REPAINT,
        ]);


/*
 * TASK TYPES
 */

defined('TASK_TYPE_PRIMARY') or define('TASK_TYPE_PRIMARY', 'primary');
defined('TASK_TYPE_REMARK') or define('TASK_TYPE_REMARK', 'remark');

/*
 * TASK INTERVENT TYPES
 */

$intervent_types = ['damaged', 'corrosion', 'other' ];  // ???

defined('TASK_INTERVENT_TYPE_DAMAGED') or define('TASK_INTERVENT_TYPE_DAMAGED', 'damaged');
defined('TASK_INTERVENT_TYPE_CORROSION') or define('TASK_INTERVENT_TYPE_CORROSION', 'corrosion');
defined('TASK_INTERVENT_TYPE_OTHER') or define('TASK_INTERVENT_TYPE_OTHER', 'other');


/*
 * VALIDATORS MESSAGES
 */

defined('VALIDATOR_REQUIRED') or define('VALIDATOR_REQUIRED', 'is required and cannot be null');
defined('VALIDATOR_STRING') or define('VALIDATOR_STRING', 'is not a string');
defined('VALIDATOR_NUMERIC') or define('VALIDATOR_NUMERIC', 'is not a numeric value');
defined('VALIDATOR_IN') or define('VALIDATOR_IN', 'is not a valid value, valid values are: ');
defined('VALIDATOR_EMAIL') or define('VALIDATOR_EMAIL', 'is not a valid email address');
defined('VALIDATOR_EMAIL_UNIQUE') or define('VALIDATOR_EMAIL_UNIQUE', 'this email address is already used in the system, please choose another one.');
defined('VALIDATOR_EXIST') or define('VALIDATOR_EXIST', 'does not exist in the database');
/*
 * RESET PASSWORD MESSAGES
 */

defined('PASSWORD_RESET_LINK_SENT') or define('PASSWORD_RESET_LINK_SENT', 'Password reset link sent via email!');


/*
 * SYSTEM FLASH MESSAGES
 */

defined('FLASH_ERROR') or define('FLASH_ERROR', 'error');
defined('FLASH_SUCCESS') or define('FLASH_SUCCESS', 'success');
defined('FLASH_WARNING') or define('FLASH_WARNING', 'warning');
defined('FLASH_INFO') or define('FLASH_INFO', 'info');


/*
 * HTTP STATUS ERRORS
 */

defined('HTTP_412_EXCEPTION_ERROR_MSG') or define('HTTP_412_EXCEPTION_ERROR_MSG', 'Some external key update has failed. [Exception msg: :exc_msg]');
defined('HTTP_412_DEL_UPD_ERROR_MSG') or define('HTTP_412_DEL_UPD_ERROR_MSG', 'The resource you are trying to delete or update has other resources related, you cannot delete it.');
defined('HTTP_412_ADD_UPD_ERROR_MSG') or define('HTTP_412_ADD_UPD_ERROR_MSG', 'The resource you are trying to add or update has other resources related, please check your data and look for missing external keys.');
defined('HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG') or define('HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG', 'The :resource you are trying to delete has some :entities related, this operation is not allowed.');
defined('CUSTOM_CODE_ERROR_BODY') or define('CUSTOM_CODE_ERROR_BODY', 'Code: :code');



/*
 * QUEUE JOB MESSAGES
 */

defined('QUEUE_TASK_CREATED') or define('QUEUE_TASK_CREATED', 'task_created');
defined('QUEUE_TASK_UPDATED') or define('QUEUE_TASK_UPDATED', 'task_updated');
defined('QUEUE_JOB_TASK_UPDATES_FAILED') or define('QUEUE_JOB_TASK_UPDATES_FAILED', '[QUEUE - JOB FAILED] Job failure when trying to notify task creation/updating. [msg: :exc_msg]');


/*
 * ENTITY TYPES TO WHICH WE CAN ADD A DOCUMENT
 */


defined('DOCUMENT_RELATED_ENTITY_PROJECT') or define('DOCUMENT_RELATED_ENTITY_PROJECT', 'project');
defined('DOCUMENT_RELATED_ENTITY_BOAT') or define('DOCUMENT_RELATED_ENTITY_BOAT', 'boat');
defined('DOCUMENT_RELATED_ENTITY_SECTION') or define('DOCUMENT_RELATED_ENTITY_SECTION', 'section');
defined('DOCUMENT_RELATED_ENTITY_TASK') or define('DOCUMENT_RELATED_ENTITY_TASK', 'task');


defined('DOCUMENT_RELATED_ENTITIES') or define('DOCUMENT_RELATED_ENTITIES',
        [
          DOCUMENT_RELATED_ENTITY_PROJECT,
          DOCUMENT_RELATED_ENTITY_BOAT,
          DOCUMENT_RELATED_ENTITY_SECTION,
          DOCUMENT_RELATED_ENTITY_TASK
         ]);



/*
 * CUSTOM APIs
 */
defined('PROJECT_TYPE_API_VALIDATION_TITLE') or define('PROJECT_TYPE_API_VALIDATION_TITLE', 'Missing or wrong parameter');
defined('PROJECT_TYPE_API_VALIDATION_MSG') or define('PROJECT_TYPE_API_VALIDATION_MSG', 'The TYPE parameter is missing, non standard or the POST data structure is not correct. Please check your data.');
defined('PROJECT_TYPE_API_NO_ACTION_MSG') or define('PROJECT_TYPE_API_NO_ACTION_MSG', 'The TYPE of the project is already setted to the value you are trying to save. Please check your data.');
defined('PROJECT_TYPE_API_PROJECT_CLOSED_MSG') or define('PROJECT_TYPE_API_PROJECT_CLOSED_MSG', 'The main project is already closed. Please check your data.');
defined('USER_PHOTO_API_NO_DOC_TITLE') or define('USER_PHOTO_API_NO_DOC_TITLE', 'Internal error.');
defined('USER_PHOTO_API_NO_DOC_MSG') or define('USER_PHOTO_API_NO_DOC_MSG', 'Something went wrong. The profile photo was not uploaded.');


/*
 * REPORTS
 */
defined('REPORT_CORROSION_MAP_SUBTYPE') or define('REPORT_CORROSION_MAP_SUBTYPE', 'corrosion_map');
defined('REPORT_CORROSION_MAP_OVERVIEW_SUBTYPE') or define('REPORT_CORROSION_MAP_OVERVIEW_SUBTYPE', 'corrosion_map_overview_only');
defined('REPORT_ENVIRONMENTAL_SUBTYPE') or define('REPORT_ENVIRONMENTAL_SUBTYPE', 'environmental_report');
defined('REPORT_APPLOG_SUBTYPE') or define('REPORT_APPLOG_SUBTYPE', 'application_log_report');


/*
 * APPLICATION LOG - PRODUCTS - REPORT ITEMS
 */
defined('PRODUCT_TYPE_PAINTING') or define('PRODUCT_TYPE_PAINTING', 'painting');
defined('APPLICATION_LOG_SECTION_TYPE_ZONES') or define('APPLICATION_LOG_SECTION_TYPE_ZONES', 'zones');
defined('APPLICATION_LOG_SECTION_TYPE_PREPARATION') or define('APPLICATION_LOG_SECTION_TYPE_PREPARATION', 'preparation');
defined('APPLICATION_LOG_SECTION_TYPE_APPLICATION') or define('APPLICATION_LOG_SECTION_TYPE_APPLICATION', 'application');
defined('APPLICATION_LOG_SECTION_TYPE_INSPECTION') or define('APPLICATION_LOG_SECTION_TYPE_INSPECTION', 'inspection');
defined('APPLICATION_TYPE_PRIMER') or define('APPLICATION_TYPE_PRIMER', 'primer');
defined('APPLICATION_TYPE_FILLER') or define('APPLICATION_TYPE_FILLER', 'filler');
defined('APPLICATION_TYPE_HIGHBUILD') or define('APPLICATION_TYPE_HIGHBUILD', 'highbuild');
defined('APPLICATION_TYPE_UNDERCOAT') or define('APPLICATION_TYPE_UNDERCOAT', 'undercoat');
defined('APPLICATION_TYPE_COATING') or define('APPLICATION_TYPE_COATING', 'topcoat');
defined('REPORT_ITEM_TYPE_APPLICATION_LOG') or define('REPORT_ITEM_TYPE_APPLICATION_LOG', 'application_log');
defined('REPORT_ITEM_TYPE_ENVIRONM_LOG') or define('REPORT_ITEM_TYPE_ENVIRONM_LOG', 'environmental_log_data');
defined('REPORT_ITEM_TYPE_ENVIRONM_DOC') or define('REPORT_ITEM_TYPE_ENVIRONM_DOC', 'environmental_log_document');
defined('REPORT_ITEM_TYPE_CORR_MAP_DOC') or define('REPORT_ITEM_TYPE_CORR_MAP_DOC', 'corrosion_map_document');
defined('REPORT_ITEM_TYPE_CORR_MAP_OV_DOC') or define('REPORT_ITEM_TYPE_CORR_MAP_OV_DOC', 'corrosion_map_overview_document');

