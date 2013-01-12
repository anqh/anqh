CREATE TABLE "public"."galleries" (
  "id" SERIAL,
  "name" VARCHAR(250) NOT NULL,
  "image_count" INTEGER DEFAULT 0,
  "dir" VARCHAR(50),
  "copyright" VARCHAR(250),
  "links" TEXT,
  "mainfile" VARCHAR(50),
  "event_id" INTEGER,
  "default_image_id" INTEGER,
  "updated" INTEGER,
  "created" INTEGER DEFAULT date_part(''epoch''::text, now()),
  "date" INTEGER,
  "comment_count" INTEGER DEFAULT 0,
  "rate_count" INTEGER DEFAULT 0,
  "rate_total" INTEGER DEFAULT 0,
  CONSTRAINT "galleries_pkey" PRIMARY KEY("id"),
  CONSTRAINT "galleries_default_image_id" FOREIGN KEY ("default_image_id")
    REFERENCES "public"."images"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "galleries_event_id" FOREIGN KEY ("event_id")
    REFERENCES "public"."events"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "galleries_date_idx" ON "public"."galleries"
  USING btree ("date" DESC);

CREATE INDEX "galleries_default_image_id_idx" ON "public"."galleries"
  USING btree ("default_image_id");

CREATE INDEX "galleries_image_count" ON "public"."galleries"
  USING btree ("image_count");
