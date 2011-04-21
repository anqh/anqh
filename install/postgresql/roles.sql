CREATE TABLE "public"."roles" (
  "id" SERIAL,
  "name" VARCHAR(32) NOT NULL,
  "description" VARCHAR(255),
  CONSTRAINT "roles_name_key" UNIQUE("name"),
  CONSTRAINT "roles_pkey" PRIMARY KEY("id")
) WITH OIDS;
