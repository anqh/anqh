CREATE TABLE "public"."user_comments" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "author_id" INTEGER NOT NULL,
  "comment" VARCHAR(300) NOT NULL,
  "private" SMALLINT DEFAULT 0 NOT NULL,
  "created" INTEGER,
  CONSTRAINT "user_comments_pkey" PRIMARY KEY("id"),
  CONSTRAINT "user_comments_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "user_comments_author_id_fkey" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITH OIDS;

CREATE INDEX "user_comments_user_id_idx" ON "public"."user_comments"
  USING btree ("user_id");

CREATE INDEX "user_comments_author_id_idx" ON "public"."user_comments"
  USING btree ("author_id");
