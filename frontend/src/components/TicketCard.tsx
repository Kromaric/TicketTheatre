import { Button, Card, Image, Text } from "@chakra-ui/react";

export const TicketCard = () => {
  return (
    <Card.Root
      maxW="sm"
      overflow="hidden"
      bg="yellow.500"
      borderWidth="1px"
      borderColor="yellow.500"
      borderRadius="lg"
    >
      <Card.Body gap="2" p="0">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          color="black"
          px="4"
          pt="4"
          pb="2"
        >
          Titre piece
        </Card.Title>

        <Image
          src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
          alt="Green double couch with wooden legs"
          w="100%"
          objectFit="cover"
          display="block"
        />

        <Card.Description color="black" px="4" pt="2">
          Réalisateur
        </Card.Description>
        <Card.Description color="black" px="4">
          Acteur
        </Card.Description>
        <Card.Description color="black" px="4" pb="2">
          Genre
        </Card.Description>

        <Text
          textStyle="2xl"
          fontWeight="medium"
          letterSpacing="tight"
          mt="2"
          color="black"
          px="4"
          pb="4"
        >
          15 €
        </Text>
      </Card.Body>

      <Card.Footer gap="2" justifyContent="center" pb="4">
        <Button variant="solid" color="white">
          Réserver
        </Button>
      </Card.Footer>
    </Card.Root>
  );
};
