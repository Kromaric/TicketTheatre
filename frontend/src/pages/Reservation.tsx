import { useMemo, useState } from "react";
import { Box, Button, Card, Flex, Input, Stack, Text } from "@chakra-ui/react";
import { useLocation, useSearchParams } from "react-router-dom";
import { TicketLabel } from "../components/TicketLabel";

type ReservationState = {
  showId?: string;
  title?: string;
  price?: number;
};

// Auth placeholder
function isAuthenticated(): boolean {
  return Boolean(
    localStorage.getItem("access_token") ||
      localStorage.getItem("token") ||
      localStorage.getItem("authToken")
  );
}

export default function Reservation() {
  const location = useLocation();
  const state = (location.state ?? {}) as ReservationState;
  const [searchParams] = useSearchParams();
  const dev = searchParams.get("dev") === "1";

  const title = state.title ?? "Titre pièce";
  const price = state.price ?? 15;

  const authed = dev || isAuthenticated();

  const [qty, setQty] = useState<number>(1);

  const total = useMemo(() => {
    const q = Number.isFinite(qty) ? qty : 1;
    return Math.max(1, q) * price;
  }, [qty, price]);

  const handleCheckout = async () => {
    // Placeholder Stripe 
    console.log("Checkout (placeholder)", { showId: state.showId, qty, price, total });
    alert("Paiement Stripe");
  };

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
              Tu dois être connecté pour réserver des places.
            </Text>
          </Card.Body>
          <Card.Footer justifyContent="center" pt="4">
            <TicketLabel
              text="Se connecter"
              to="/login"
            />
          </Card.Footer>
        </Card.Root>
      </Flex>
    );
  }

  return (
    <Flex justify="center" align="center" minH="60vh" px={4}>
      <Card.Root w="full" maxW="640px" bg="yellow.500" color="black" borderRadius="lg" p="6">
        <Card.Header pb="4">
          <Card.Title textAlign="center" textTransform="uppercase" fontSize="lg">
            Réservation
          </Card.Title>
          <Text textAlign="center" mt="2" fontWeight="semibold">
            {title}
          </Text>
        </Card.Header>

        <Card.Body>
          <Stack gap="5">
            <Flex justify="space-between" align="center">
              <Text fontWeight="semibold">Prix unitaire</Text>
              <Text>{price} €</Text>
            </Flex>

            <Flex justify="space-between" align="center" gap={4}>
              <Box>
                <Text fontWeight="semibold">Nombre de places</Text>
                <Text fontSize="sm" opacity={0.8}>
                  (min 1)
                </Text>
              </Box>

              <Input
                type="number"
                min={1}
                value={qty}
                onChange={(e) => setQty(Math.max(1, Number(e.target.value || 1)))}
                w="120px"
                bg="white"
                color="black"
              />
            </Flex>

            <Box h="1px" bg="blackAlpha.300" />

            <Flex justify="space-between" align="center">
              <Text fontWeight="bold" fontSize="lg">
                Total
              </Text>
              <Text fontWeight="bold" fontSize="lg">
                {total} €
              </Text>
            </Flex>

            <Button
              w="full"
              bg="red.800"
              color="white"
              _hover={{ bg: "red.700" }}
              onClick={handleCheckout}
            >
              Procéder au paiement
            </Button>
          </Stack>
        </Card.Body>
      </Card.Root>
    </Flex>
  );
}
