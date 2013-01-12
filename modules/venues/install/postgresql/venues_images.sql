CREATE TABLE "public"."venues_images" (
  "id" INTEGER DEFAULT nextval('images_venues_id_seq'::regclass) NOT NULL,
  "venue_id" INTEGER NOT NULL,
  "image_id" INTEGER NOT NULL,
  CONSTRAINT "venues_images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "venues_images_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "venues_images_venue_id" FOREIGN KEY ("venue_id")
    REFERENCES "public"."venues"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;
