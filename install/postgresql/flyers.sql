CREATE TABLE "public"."flyers" (
  "id" SERIAL,
  "event_id" INTEGER,
  "image_id" INTEGER NOT NULL,
  "stamp_begin" INTEGER,
  "name" VARCHAR(250),
  CONSTRAINT "flyers_images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "flyers_event_id" FOREIGN KEY ("event_id")
    REFERENCES "public"."events"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "flyers_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
