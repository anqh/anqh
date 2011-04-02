CREATE TABLE "public"."users_images" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "image_id" INTEGER NOT NULL,
  CONSTRAINT "users_images_pkey" PRIMARY KEY("id"),
  CONSTRAINT "images_users_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "images_users_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
