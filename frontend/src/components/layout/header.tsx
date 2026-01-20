import {
  Box,
  Flex,
  Heading,
  Link,
  Menu,
  Button,
  Portal,
  Icon,
  Text,
} from "@chakra-ui/react";
import { BiMenu, BiUser, BiLogOut, BiBookmark } from "react-icons/bi";
import { Link as RouterLink, useNavigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";

export default function Header() {
  const navigate = useNavigate();
  const { user, isAuthenticated, logout } = useAuth();

  const handleLogout = async () => {
    await logout();
    navigate("/");
  };

  return (
    <Box
      as="header"
      w="full"
      position="sticky"
      top="0"
      p="4"
      zIndex="1000"
      bg="rgba(0, 0, 0, 0.65)"
      backdropFilter="blur(8px)"
      boxShadow="sm"
    >
      <Flex alignItems="center" justifyContent="space-between" w="full">
        <Box p="2">
          <Link asChild _hover={{ textDecoration: "none" }}>
            <RouterLink to="/">
              <Heading size="md">Le Velum</Heading>
            </RouterLink>
          </Link>
        </Box>

        {/* Desktop */}
        <Box
          bg="red.800"
          borderRadius="full"
          display="none"
          sm={{ display: "block" }}
        >
          <Flex alignItems="center" color="white" gap="4">
            {isAuthenticated ? (
              <>
                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/mes-reservations">
                    Mes réservations
                  </RouterLink>
                </Link>

                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/programme">Le Programme</RouterLink>
                </Link>

                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/theatre">Le Théâtre</RouterLink>
                </Link>

                <Menu.Root>
                  <Menu.Trigger asChild>
                    <Button
                      variant="ghost"
                      size="sm"
                      color="white"
                      _hover={{ bg: "red.700" }}
                      borderRadius="full"
                    >
                      <Icon fontSize="xl">
                        <BiUser />
                      </Icon>
                      <Text fontSize="sm">{user?.first_name}</Text>
                    </Button>
                  </Menu.Trigger>

                  <Portal>
                    <Menu.Positioner>
                      <Menu.Content bg="red.800" color="white">
                        <Menu.Item value="logout" onClick={handleLogout}>
                          <Icon>
                            <BiLogOut />
                          </Icon>
                          Déconnexion
                        </Menu.Item>
                      </Menu.Content>
                    </Menu.Positioner>
                  </Portal>
                </Menu.Root>
              </>
            ) : (
              <>
                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/connexion">Connexion</RouterLink>
                </Link>

                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/programme">Le Programme</RouterLink>
                </Link>

                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/theatre">Le Théatre</RouterLink>
                </Link>

                <Link
                  asChild
                  p="2"
                  _hover={{
                    textDecoration: "none",
                    bg: "red.700",
                    borderRadius: "full",
                  }}
                >
                  <RouterLink to="/mes-reservations">
                    Mes réservations
                  </RouterLink>
                </Link>
              </>
            )}
          </Flex>
        </Box>

        {/* Mobile */}
        <Box sm={{ display: "none" }} display="block">
          <Menu.Root>
            <Menu.Trigger asChild>
              <Button
                variant="outline"
                size="md"
                borderRadius="full"
                bg="red.800"
                color="white"
                _hover={{ bg: "red.700" }}
              >
                Menu
                <Icon>
                  <BiMenu />
                </Icon>
              </Button>
            </Menu.Trigger>

            <Portal>
              <Menu.Positioner>
                <Menu.Content bg="red.800" color="white">
                  {isAuthenticated ? (
                    <>
                      <Menu.Item value="user" disabled>
                        <Icon>
                          <BiUser />
                        </Icon>
                        {user?.full_name}
                      </Menu.Item>
                      <Menu.Separator />
                      <Menu.Item
                        value="reservations"
                        onClick={() => navigate("/mes-reservations")}
                      >
                        <Icon>
                          <BiBookmark />
                        </Icon>
                        Mes réservations
                      </Menu.Item>
                      <Menu.Item
                        value="programme"
                        onClick={() => navigate("/programme")}
                      >
                        Le Programme
                      </Menu.Item>
                      <Menu.Item
                        value="theatre"
                        onClick={() => navigate("/theatre")}
                      >
                        Le Théâtre
                      </Menu.Item>
                      <Menu.Separator />
                      <Menu.Item value="logout" onClick={handleLogout}>
                        <Icon>
                          <BiLogOut />
                        </Icon>
                        Déconnexion
                      </Menu.Item>
                    </>
                  ) : (
                    <>
                      <Menu.Item
                        value="connexion"
                        onClick={() => navigate("/connexion")}
                      >
                        Connexion
                      </Menu.Item>
                      <Menu.Item
                        value="programme"
                        onClick={() => navigate("/programme")}
                      >
                        Le Programme
                      </Menu.Item>
                      <Menu.Item
                        value="theatre"
                        onClick={() => navigate("/theatre")}
                      >
                        Le Théâtre
                      </Menu.Item>
                    </>
                  )}
                </Menu.Content>
              </Menu.Positioner>
            </Portal>
          </Menu.Root>
        </Box>
      </Flex>
    </Box>
  );
}
