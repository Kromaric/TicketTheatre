import { Card, Input, Stack } from "@chakra-ui/react";
import { TicketLabel } from "./TicketLabel";

export const SignupCard = () => {
  return (
    <Card.Root
      maxW="260px"          // largeur globale plus petite
      bg="yellow.500"
      color="black"
      borderRadius="md"     // un peu moins arrondi que lg
      p="3"                 // moins de padding
    >
      <Card.Header pb="1">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          fontSize="sm"     // plus petit que md
        >
          Créer son compte
        </Card.Title>
      </Card.Header>

      <Card.Body pt="2" pb="2">
        <Stack gap="2" w="full"> {/* moins d’espace entre les champs */}
          <Input
            type="text"
            placeholder="Nom"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="text"
            placeholder="Prénom"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="email"
            placeholder="Email"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="tel"
            placeholder="Téléphone"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="date"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="text"
            placeholder="Sexe"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="password"
            placeholder="Mot de passe"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />

          <Input
            type="password"
            placeholder="Confirmation mot de passe"
            bg="white"
            color="black"
            size="xs"
            fontSize="0.6rem"
          />
        </Stack>
      </Card.Body>

      <Card.Footer justifyContent="center" pt="2">
        <TicketLabel text="Créer son compte" />
      </Card.Footer>
    </Card.Root>
  );
};
