import { useMemo, useState } from "react";
import { Box, Grid } from "@chakra-ui/react";
import Hero from "../components/Hero";
import { TicketCard } from "../components/TicketCard";
import { TicketPagination } from "../components/TicketPagination";

export default function Home() {
  const [page, setPage] = useState(1);

  // Données statiques
  const totalTickets = 20;
  const pageSize = 6;
  const pageCount = Math.ceil(totalTickets / pageSize);

  const tickets = useMemo(
    () => Array.from({ length: totalTickets }, (_, i) => i),
    []
  );

  const visibleTickets = tickets.slice((page - 1) * pageSize, page * pageSize);

  return (
    <>
      <Hero />

      <Box w="full" maxW="1200px" mx="auto" mt={6}>
        <Grid
          templateColumns={{ base: "1fr", md: "repeat(3, 1fr)" }}
          gap={6}
          justifyItems="center"
        >
          {visibleTickets.map((i) => (
            <TicketCard key={i} />
          ))}
        </Grid>

        <TicketPagination
          page={page}
          pageCount={pageCount}
          onPageChange={setPage}
        />
      </Box>
    </>
  );
}
