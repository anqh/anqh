CREATE TABLE "public"."forum_private_recipients" (
  "forum_topic_id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "forum_area_id" INTEGER NOT NULL,
  "unread" INTEGER DEFAULT 0 NOT NULL,
  "id" SERIAL,
  CONSTRAINT "forum_private_recipients_pkey" PRIMARY KEY("id"),
  CONSTRAINT "forum_private_recipients_user_id" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "forum_private_recipients_topic_id" FOREIGN KEY ("forum_topic_id")
    REFERENCES "public"."forum_private_topics"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;

ALTER TABLE "public"."forum_private_recipients"
  ALTER COLUMN "user_id" SET STATISTICS 0;

CREATE INDEX "forum_private_recipients_topic_id" ON "public"."forum_private_recipients"
  USING btree ("forum_topic_id");

CREATE INDEX "forum_private_recipients_user_id" ON "public"."forum_private_recipients"
  USING btree ("user_id", "unread" DESC);
