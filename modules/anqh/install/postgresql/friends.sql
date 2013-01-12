CREATE TABLE "public"."friends" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "friend_id" INTEGER NOT NULL,
  "created2" TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
  "created" INTEGER DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT "friends_pkey" PRIMARY KEY("id"),
  CONSTRAINT "friends_friend_id_fkey" FOREIGN KEY ("friend_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "friends_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "friends_friend_id_idx" ON "public"."friends"
  USING btree ("friend_id");

CREATE INDEX "friends_user_id_idx" ON "public"."friends"
  USING btree ("user_id");
