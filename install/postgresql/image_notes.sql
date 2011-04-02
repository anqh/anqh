CREATE TABLE "public"."image_notes" (
  "id" SERIAL,
  "image_id" INTEGER NOT NULL,
  "author_id" INTEGER,
  "name" VARCHAR(30) NOT NULL,
  "user_id" INTEGER,
  "x" INTEGER,
  "y" INTEGER,
  "width" INTEGER,
  "height" INTEGER,
  "created" INTEGER,
  "new_comment_count" INTEGER,
  "new_note" INTEGER,
  CONSTRAINT "image_notes_pkey" PRIMARY KEY("id"),
  CONSTRAINT "image_notes_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "image_notes_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "image_notes_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "image_notes_image_id_x_idx" ON "public"."image_notes"
  USING btree ("image_id", "x");

CREATE INDEX "image_notes_user_id_idx" ON "public"."image_notes"
  USING btree ("user_id");
