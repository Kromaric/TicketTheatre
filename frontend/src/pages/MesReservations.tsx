import { Box, Card, Flex, Grid, Text } from "@chakra-ui/react";
import { TicketLabel } from "../components/TicketLabel";
import { useSearchParams } from "react-router-dom";

// Auth placeholder (service d'authentification)
function isAuthenticated(): boolean {
  return Boolean(
    localStorage.getItem("access_token") ||
      localStorage.getItem("token") ||
      localStorage.getItem("authToken")
  );
}

type ReservationItem = {
  id: string;
  title: string;
  dateLabel: string;  
  qty: number;
  unitPrice: number;
  status: "confirmée" | "en attente" | "annulée";
};

export default function MesReservations() {
  const [searchParams] = useSearchParams();
  const dev = searchParams.get("dev") === "1";
  const authed = dev || isAuthenticated();

  // Données statiques (à remplacer par un fetch)
  const reservations: ReservationItem[] = [
    {
      id: "R-1024",
      title: "Titre pièce",
      dateLabel: "Ven. 09 — 20:30",
      qty: 2,
      unitPrice: 15,
      status: "confirmée",
    },
    {
      id: "R-1025",
      title: "Titre pièce",
      dateLabel: "Sam. 10 — 18:00",
      qty: 1,
      unitPrice: 15,
      status: "en attente",
    },
  ];

  if (!authed) {
    return (
      <Flex justify="center" align="center" minH="60vh" px={4}>
        <Card.Root w="full" maxW="520px" bg="yellow.500" color="black" borderRadius="lg" p="6">
          <Card.Header pb="3">
            <Card.Title textAlign="center" textTransform="uppercase">
              Connexion requise
            </Card.Title>
          </Card.Header>

          <Card.Body>
            <Text textAlign="center">
              Tu dois être connecté pour accéder à tes réservations.
            </Text>
          </Card.Body>

          <Card.Footer justifyContent="center" pt="4">
            <TicketLabel text="Se connecter" to="/connexion" />
          </Card.Footer>
        </Card.Root>
      </Flex>
    );
  }

  return (
    <Box w="full" maxW="1200px" mx="auto" pt={4} pb={8}>
      <Text fontWeight="bold" fontSize="xl" mb={6} textAlign="center">
        Mes réservations
      </Text>

      {reservations.length === 0 ? (
        <Flex justify="center" mt={10}>
          <Card.Root w="full" maxW="520px" bg="yellow.500" color="black" borderRadius="lg" p="6">
            <Card.Body>
              <Text textAlign="center" fontWeight="semibold">
                Aucune réservation pour le moment.
              </Text>
            </Card.Body>
            <Card.Footer justifyContent="center" pt="4">
              <TicketLabel text="Voir le programme" to="/programme" />
            </Card.Footer>
          </Card.Root>
        </Flex>
      ) : (
        <Grid
          templateColumns={{ base: "1fr", md: "repeat(2, 1fr)" }}
          gap={6}
          justifyItems="center"
        >
          {reservations.map((r) => {
            const total = r.qty * r.unitPrice;
            const statusColor =
              r.status === "confirmée" ? "green.600" : r.status === "en attente" ? "orange.600" : "red.600";

            return (
              <Card.Root
                key={r.id}
                w="full"
                maxW="520px"
                bg="yellow.500"
                color="black"
                borderRadius="lg"
                overflow="hidden"
              >
                <Card.Header pb="2">
                  <Card.Title textTransform="uppercase" textAlign="center">
                    {r.title}
                  </Card.Title>
                  <Text textAlign="center" fontSize="sm" opacity={0.9}>
                    {r.dateLabel}
                  </Text>
                </Card.Header>

                <Card.Body>
                  <Flex justify="space-between" mb={2}>
                    <Text fontWeight="semibold">Réservation</Text>
                    <Text>{r.id}</Text>
                  </Flex>

                  <Flex justify="space-between" mb={2}>
                    <Text fontWeight="semibold">Places</Text>
                    <Text>{r.qty}</Text>
                  </Flex>

                  <Flex justify="space-between" mb={2}>
                    <Text fontWeight="semibold">Prix unitaire</Text>
                    <Text>{r.unitPrice} €</Text>
                  </Flex>

                  <Flex justify="space-between" mb={2}>
                    <Text fontWeight="bold">Total</Text>
                    <Text fontWeight="bold">{total} €</Text>
                  </Flex>

                  <Flex justify="space-between" align="center" mt={4}>
                    <Text fontWeight="semibold">Statut</Text>
                    <Text fontWeight="bold" color={statusColor}>
                      {r.status.toUpperCase()}
                    </Text>
                  </Flex>
                </Card.Body>

                <Card.Footer justifyContent="center" pb="4">
                  <TicketLabel text="Voir le programme" to="/programme" variant="red" />
                </Card.Footer>
              </Card.Root>
            );
          })}
        </Grid>
      )}
    </Box>
  );
}
