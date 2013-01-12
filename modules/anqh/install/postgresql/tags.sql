CREATE TABLE "public"."tags" (
  "id" SERIAL,
  "tag_group_id" INTEGER NOT NULL,
  "name" VARCHAR(32) NOT NULL,
  "description" VARCHAR(250),
  "author_id" INTEGER,
  "created" TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
  CONSTRAINT "tags_pkey" PRIMARY KEY("id"),
  CONSTRAINT "tags_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE NO ACTION
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "tags_tag_group_id" FOREIGN KEY ("tag_group_id")
    REFERENCES "public"."tag_groups"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
