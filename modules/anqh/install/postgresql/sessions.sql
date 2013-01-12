CREATE TABLE "public"."sessions" (
  "id" VARCHAR(40) NOT NULL,
  "last_active" INTEGER NOT NULL,
  "contents" TEXT NOT NULL,
  CONSTRAINT "session_pkey" PRIMARY KEY("id")
) WITH OIDS;
