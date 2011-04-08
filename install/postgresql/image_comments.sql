CREATE TABLE "public"."image_comments" (
  "id" SERIAL,
  "image_id" INTEGER NOT NULL,
  "author_id" INTEGER NOT NULL,
  "user_id" INTEGER,
  "comment" VARCHAR(255),
  "private" SMALLINT DEFAULT 0 NOT NULL,
  "created" INTEGER,
  CONSTRAINT "image_comments_pkey" PRIMARY KEY("id"),
  CONSTRAINT "image_comments_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "image_comments_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "image_comments_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "image_comments_author_id" ON "public"."image_comments"
  USING btree ("author_id");

CREATE INDEX "image_comments_image_id" ON "public"."image_comments"
  USING btree ("image_id");
