-- Table: migrations
CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('4', '2025_11_03_152800_create_profiles_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('5', '2025_11_03_154600_create_personal_access_tokens_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('6', '2025_11_04_090910_create_job_postings_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('7', '2025_11_04_092948_create_events_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('8', '2025_11_04_094349_create_forum_threads_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('9', '2025_11_04_094440_create_forum_replies_table', '1');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('10', '2026_04_04_112200_add_image_path_to_jobs_and_forum', '2');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('11', '2026_04_04_112727_create_posts_table', '3');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('12', '2026_04_04_112729_create_post_comments_table', '3');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('13', '2026_04_04_112730_create_post_likes_table', '3');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('14', '2026_04_04_113135_create_conversations_table', '4');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('15', '2026_04_04_113137_create_messages_table', '4');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('16', '2026_04_04_113139_create_work_groups_table', '4');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('17', '2026_04_04_113140_create_work_group_members_table', '4');
INSERT INTO "migrations" ("id", "migration", "batch") VALUES ('18', '2026_04_04_120608_make_posts_body_nullable', '5');


-- Table: users
CREATE TABLE "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "promotion_year" integer, "role" varchar not null default 'alumni', "remember_token" varchar, "created_at" datetime, "updated_at" datetime);

INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "promotion_year", "role", "remember_token", "created_at", "updated_at") VALUES ('1', 'Test User', 'test@example.com', '2026-04-04 10:37:52', '$2y$12$lNgC.Rjfa4AeEQbmNHoL6eVCaSvOWiPgfhxUdSpZodullqI0QktlC', NULL, 'alumni', 'qa8yQssVsa', '2026-04-04 10:37:52', '2026-04-04 10:37:52');
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "promotion_year", "role", "remember_token", "created_at", "updated_at") VALUES ('2', 'assane', 'bniasseisidp@groupeisi.com', NULL, '$2y$12$2xjJNKnjXlbXmzt90KeGQ.wAvANaoTcrKQ2RS./r9NltDe9.wJZzy', '2026', 'alumni', NULL, '2026-04-04 10:53:58', '2026-04-04 10:53:58');


-- Table: password_reset_tokens
CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));



-- Table: sessions
CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

INSERT INTO "sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES ('TU8hK5Y2l9UUKDqr6y20Q8OP94IWFuhQDBTp2d9f', NULL, '127.0.0.1', 'curl/8.13.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoidzdjVlpTRFlaU05DOXdaNHVESmhVbzBSZGRtSW5VbGp6NXBab1d4MSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1775299739');


-- Table: cache
CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));



-- Table: cache_locks
CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));



-- Table: jobs
CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);



-- Table: job_batches
CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));



-- Table: failed_jobs
CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);



-- Table: profiles
CREATE TABLE "profiles" ("id" integer primary key autoincrement not null, "user_id" integer not null, "bio" text, "job_title" varchar, "company_name" varchar, "city" varchar, "linkedin_url" varchar, "profile_picture_url" varchar, "is_visible" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO "profiles" ("id", "user_id", "bio", "job_title", "company_name", "city", "linkedin_url", "profile_picture_url", "is_visible", "created_at", "updated_at") VALUES ('1', '2', 'Bienvenue sur ISI-Connect ! Mettez à jour votre profil.', NULL, NULL, NULL, NULL, NULL, '1', '2026-04-04 10:53:58', '2026-04-04 10:53:58');


-- Table: personal_access_tokens
CREATE TABLE "personal_access_tokens" ("id" integer primary key autoincrement not null, "tokenable_type" varchar not null, "tokenable_id" integer not null, "name" text not null, "token" varchar not null, "abilities" text, "last_used_at" datetime, "expires_at" datetime, "created_at" datetime, "updated_at" datetime);

INSERT INTO "personal_access_tokens" ("id", "tokenable_type", "tokenable_id", "name", "token", "abilities", "last_used_at", "expires_at", "created_at", "updated_at") VALUES ('3', 'App\Models\User', '2', 'auth_token', '59220afb937234acb57a009210d9464e822c5b5a866c508814d663efc64342f7', '["*"]', '2026-04-04 11:37:12', NULL, '2026-04-04 11:27:45', '2026-04-04 11:37:12');
INSERT INTO "personal_access_tokens" ("id", "tokenable_type", "tokenable_id", "name", "token", "abilities", "last_used_at", "expires_at", "created_at", "updated_at") VALUES ('4', 'App\Models\User', '2', 'auth_token', 'e5f5298d0180fae722ead8feb359b920597b3a33ba96fd34df6f35c7b03d6c02', '["*"]', '2026-04-04 12:26:18', NULL, '2026-04-04 11:38:08', '2026-04-04 12:26:18');
INSERT INTO "personal_access_tokens" ("id", "tokenable_type", "tokenable_id", "name", "token", "abilities", "last_used_at", "expires_at", "created_at", "updated_at") VALUES ('5', 'App\Models\User', '2', 'auth_token', '81eee009459d9592f1943dd09a29c1aedce1d8c11a6b0d5fe6dc5970afbc68c1', '["*"]', '2026-04-04 12:29:58', NULL, '2026-04-04 12:29:43', '2026-04-04 12:29:58');


-- Table: job_postings
CREATE TABLE "job_postings" ("id" integer primary key autoincrement not null, "user_id" integer not null, "title" varchar not null, "company_name" varchar not null, "description" text not null, "location" varchar not null, "type" varchar not null, "apply_url" varchar, "apply_email" varchar, "created_at" datetime, "updated_at" datetime, "image_path" varchar, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: events
CREATE TABLE "events" ("id" integer primary key autoincrement not null, "user_id" integer not null, "title" varchar not null, "description" text not null, "location" varchar not null, "starts_at" datetime not null, "ends_at" datetime, "cover_image_url" varchar, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO "events" ("id", "user_id", "title", "description", "location", "starts_at", "ends_at", "cover_image_url", "created_at", "updated_at") VALUES ('1', '2', 'h', 'hejfm', 'dakar', '2026-04-19 12:11:00', NULL, NULL, '2026-04-04 12:11:57', '2026-04-04 12:11:57');


-- Table: forum_threads
CREATE TABLE "forum_threads" ("id" integer primary key autoincrement not null, "user_id" integer not null, "title" varchar not null, "body" text not null, "created_at" datetime, "updated_at" datetime, "image_path" varchar, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: forum_replies
CREATE TABLE "forum_replies" ("id" integer primary key autoincrement not null, "forum_thread_id" integer not null, "user_id" integer not null, "body" text not null, "created_at" datetime, "updated_at" datetime, foreign key("forum_thread_id") references "forum_threads"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: post_comments
CREATE TABLE "post_comments" ("id" integer primary key autoincrement not null, "post_id" integer not null, "user_id" integer not null, "body" text not null, "created_at" datetime, "updated_at" datetime, foreign key("post_id") references "posts"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: post_likes
CREATE TABLE "post_likes" ("id" integer primary key autoincrement not null, "post_id" integer not null, "user_id" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("post_id") references "posts"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: conversations
CREATE TABLE "conversations" ("id" integer primary key autoincrement not null, "user_one_id" integer not null, "user_two_id" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("user_one_id") references "users"("id") on delete cascade, foreign key("user_two_id") references "users"("id") on delete cascade);

INSERT INTO "conversations" ("id", "user_one_id", "user_two_id", "created_at", "updated_at") VALUES ('1', '2', '2', '2026-04-04 12:21:20', '2026-04-04 12:21:20');


-- Table: messages
CREATE TABLE "messages" ("id" integer primary key autoincrement not null, "conversation_id" integer not null, "sender_id" integer not null, "body" text not null, "is_read" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("conversation_id") references "conversations"("id") on delete cascade, foreign key("sender_id") references "users"("id") on delete cascade);

INSERT INTO "messages" ("id", "conversation_id", "sender_id", "body", "is_read", "created_at", "updated_at") VALUES ('1', '1', '2', 'hello', '0', '2026-04-04 12:21:33', '2026-04-04 12:21:33');
INSERT INTO "messages" ("id", "conversation_id", "sender_id", "body", "is_read", "created_at", "updated_at") VALUES ('2', '1', '2', 'hello', '0', '2026-04-04 12:21:37', '2026-04-04 12:21:37');
INSERT INTO "messages" ("id", "conversation_id", "sender_id", "body", "is_read", "created_at", "updated_at") VALUES ('3', '1', '2', 'hello', '0', '2026-04-04 12:21:39', '2026-04-04 12:21:39');
INSERT INTO "messages" ("id", "conversation_id", "sender_id", "body", "is_read", "created_at", "updated_at") VALUES ('4', '1', '2', '📞 INITIATION APPEL VIDÉO : https://meet.jit.si/ISI_CONNECT_CALL_2_2', '0', '2026-04-04 12:24:53', '2026-04-04 12:24:53');


-- Table: work_groups
CREATE TABLE "work_groups" ("id" integer primary key autoincrement not null, "creator_id" integer not null, "name" varchar not null, "description" text, "image_path" varchar, "is_private" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("creator_id") references "users"("id") on delete cascade);



-- Table: work_group_members
CREATE TABLE "work_group_members" ("id" integer primary key autoincrement not null, "work_group_id" integer not null, "user_id" integer not null, "role" varchar not null default 'member', "created_at" datetime, "updated_at" datetime, foreign key("work_group_id") references "work_groups"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);



-- Table: posts
CREATE TABLE "posts" ("id" integer primary key autoincrement not null, "user_id" integer not null, "body" text, "image_path" varchar, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references users("id") on delete cascade on update no action);



