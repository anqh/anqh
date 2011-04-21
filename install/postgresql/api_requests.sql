CREATE TABLE "public"."api_requests" (
  "id" SERIAL,
  "ip" VARCHAR(15),
  "created" INTEGER,
  "request" TEXT,
  CONSTRAINT "api_requests_pkey" PRIMARY KEY("id")
) WITH OIDS;

CREATE INDEX "api_requests_ip_created_idx" ON "public"."api_requests"
  USING btree ("ip", "created");
