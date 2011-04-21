CREATE TABLE "public"."forum_quotes" (
  "user_id" INTEGER NOT NULL,
  "forum_topic_id" INTEGER NOT NULL,
  "forum_post_id" INTEGER NOT NULL,
  "author_id" INTEGER NOT NULL,
  "created" INTEGER,
  "id" SERIAL,
  CONSTRAINT "forum_quotes_pkey" PRIMARY KEY("id"),
  CONSTRAINT "forum_quotes_member_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "forum_quotes_quoter_id_fkey" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "forum_quotes_topic_id_post_id" FOREIGN KEY ("forum_topic_id", "forum_post_id")
    REFERENCES "public"."forum_posts"("forum_topic_id", "id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "forum_quotes_topic_id" ON "public"."forum_quotes"
  USING btree ("forum_topic_id");

CREATE INDEX "forum_quotes_user_id" ON "public"."forum_quotes"
  USING btree ("user_id");
