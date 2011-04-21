CREATE TABLE "public"."logins" (
  "id" SERIAL,
  "user_id" INTEGER,
  "username" TEXT,
  "ip" VARCHAR(15),
  "hostname" TEXT,
  "success" SMALLINT DEFAULT 0,
  "password" SMALLINT DEFAULT 0,
  "stamp" INTEGER DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT "logins_pkey" PRIMARY KEY("id")
) WITH OIDS;
