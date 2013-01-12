CREATE TABLE "public"."online_users" (
  "id" VARCHAR(40) NOT NULL,
  "last_activity" INTEGER NOT NULL,
  "user_id" INTEGER,
  CONSTRAINT "online_users_pkey" PRIMARY KEY("id"),
  CONSTRAINT "online_users_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
