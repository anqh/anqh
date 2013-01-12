CREATE TABLE "public"."roles_users" (
  "user_id" INTEGER NOT NULL,
  "role_id" INTEGER NOT NULL,
  "expires" TIMESTAMP WITHOUT TIME ZONE,
  "created" TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
  "id" SERIAL,
  CONSTRAINT "roles_users_pkey" PRIMARY KEY("id"),
  CONSTRAINT "users_roles_idx" UNIQUE("user_id", "role_id"),
  CONSTRAINT "users_roles_role_id_fkey" FOREIGN KEY ("role_id")
    REFERENCES "public"."roles"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "users_roles_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITH OIDS;
