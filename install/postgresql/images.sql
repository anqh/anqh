CREATE TABLE "public"."images" (
  "id" SERIAL,
  "status" CHAR(1) DEFAULT 'v'::bpchar NOT NULL,
  "author_id" INTEGER,
  "view_count" INTEGER DEFAULT 0,
  "last_view" TIMESTAMP WITHOUT TIME ZONE,
  "comment_count" INTEGER DEFAULT 0,
  "rate_count" INTEGER DEFAULT 0,
  "rate_total" INTEGER DEFAULT 0,
  "description" VARCHAR(250),
  "original_size" INTEGER,
  "original_width" SMALLINT,
  "original_height" SMALLINT,
  "width" SMALLINT,
  "height" SMALLINT,
  "thumb_width" SMALLINT,
  "thumb_height" SMALLINT,
  "format" VARCHAR(3) DEFAULT 'jpg'::bpchar,
  "legacy_filename" VARCHAR(64),
  "file" VARCHAR(250),
  "created" INTEGER,
  "modified" INTEGER,
  "postfix" VARCHAR(8),
  "new_comment_count" INTEGER,
  "rand" INTEGER DEFAULT (random() * 10000::double precision)::numeric,
  "remote" VARCHAR(250),
  "description_old" VARCHAR(250),
  CONSTRAINT "images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "images_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

COMMENT ON COLUMN "public"."images"."status"
IS 'n = not accepted, d = deleted, h = hidden, v = visible';

CREATE INDEX "images_author_id_idx" ON "public"."images"
  USING btree ("author_id");

CREATE INDEX "images_rand_idx" ON "public"."images"
  USING btree ("rand", "status");
