CREATE TABLE "public"."events_tags" (
  "id" SERIAL,
  "event_id" INTEGER NOT NULL,
  "tag_id" INTEGER NOT NULL,
  CONSTRAINT "events_tags_pkey" PRIMARY KEY("id"),
  CONSTRAINT "events_tags_event_id" FOREIGN KEY ("event_id")
    REFERENCES "public"."events"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "events_tags_tag_id" FOREIGN KEY ("tag_id")
    REFERENCES "public"."tags"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
