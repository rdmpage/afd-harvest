# Updating records

AFD UUIDs can change for names and taxa, to fetch these we do HTTP GET on an existing UUID, and if we get a 302 then we fetch the revised data.