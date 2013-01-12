CREATE TABLE "public"."shouts" (
  "id" SERIAL,
  "author_id" INTEGER NOT NULL,
  "shout" VARCHAR(300) NOT NULL,
  "created" INTEGER DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT "shouts_pkey" PRIMARY KEY("id"),
  CONSTRAINT "shouts_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;
