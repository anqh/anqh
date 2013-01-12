CREATE TABLE "public"."galleries_images" (
  "id" SERIAL,
  "gallery_id" INTEGER NOT NULL,
  "image_id" INTEGER NOT NULL,
  CONSTRAINT "galleries_images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "galleries_images_gallery_id" FOREIGN KEY ("gallery_id")
    REFERENCES "public"."galleries"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "galleries_images_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;

CREATE INDEX "galleries_images_idx" ON "public"."galleries_images"
  USING btree ("image_id");
