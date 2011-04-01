CREATE TABLE "public"."events_images" (
  "id" SERIAL,
  "event_id" INTEGER NOT NULL,
  "image_id" INTEGER NOT NULL,
  CONSTRAINT "events_images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "events_images_event_id" FOREIGN KEY ("event_id")
    REFERENCES "public"."events"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "events_images_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;
