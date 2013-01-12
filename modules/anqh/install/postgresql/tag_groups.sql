CREATE TABLE "public"."tag_groups" (
  "id" SERIAL,
  "name" VARCHAR(32) NOT NULL,
  "description" VARCHAR(250),
  "author_id" INTEGER,
  "created" TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT now(),
  CONSTRAINT "tag_groups_group_key" UNIQUE("name"),
  CONSTRAINT "tag_groups_pkey" PRIMARY KEY("id"),
  CONSTRAINT "tag_groups_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE NO ACTION
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;

ALTER TABLE "public"."tag_groups"
  ALTER COLUMN "id" SET STATISTICS 0;
