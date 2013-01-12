CREATE TABLE "public"."newsfeeditems" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "stamp" INTEGER NOT NULL,
  "class" VARCHAR(64),
  "type" VARCHAR(64),
  "data" TEXT,
  CONSTRAINT "newsfeeditems_pkey" PRIMARY KEY("id"),
  CONSTRAINT "newsfeeditems_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
